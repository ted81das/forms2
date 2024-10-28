<?php

namespace App\Http\Controllers;

use App\Form;
use App\FormData;
use App\Mail\FormSubmitted;
use App\Visitor;
use Carbon\Carbon;
use DB;
use DNS1D;
use DNS2D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Newsletter;
use PDF;
use Spatie\WebhookServer\WebhookCall;
use Image;

class FormDataController extends Controller
{
    public function store($form_id, Request $request)
    {
        try {

            //TODO: Check if form allowed to submit without login or not.
            $form = Form::findOrFail($form_id);

            $post_submit_action['notification'] = $form->schema['settings']['notification'];
            $is_enable_recaptcha = $form->schema['settings']['recaptcha']['is_enable'];
            $form_data = [];
            parse_str($request->get('form_data'), $form_data['data']);

            //if form is template don't save data
            if ($form->is_template) {
                return $this->respondSuccess($message = null, $post_submit_action);
            }

            //Verification for google reCaptcha
            if (isset($is_enable_recaptcha) && $is_enable_recaptcha == 1) {
                if (isset($form_data['data']['g-recaptcha-response']) && ! empty($form_data['data']['g-recaptcha-response'])) {
                    //your site secret key
                    $secret_key = $form->schema['settings']['recaptcha']['secret_key'];
                    //get verify response data
                    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$form_data['data']['g-recaptcha-response']);

                    $response_data = json_decode($verify_response);

                    if (! $response_data->success) {
                        $msg = 'reCaptcha error';

                        return $this->respondWithError($msg);
                    }
                } else {
                    $msg = 'reCaptcha error';

                    return $this->respondWithError($msg);
                }
            }

            //If user is logged in then save user id.
            if (Auth::check()) {
                $form_data['submitted_by'] = $request->user()->id;
            }

            $form_data['source'] = 'app';

            //status: if form data is draft(incomplete) or not
            $form_data['status'] = 'complete';
            if (isset($form_data['data']['status']) && $form_data['data']['status'] == 'incomplete') {
                $form_data['status'] = 'incomplete';
            }

            //if token, get existing data for token
            $existing_token_form_data = [];
            if (! empty($request->get('token'))) {
                $existing_token_form_data = FormData::where('form_id', $form->id)
                    ->where('token', $request->get('token'))
                    ->findOrFail($request->get('form_data_id'));
            }

            //if draft(incomplete) generate token & edit form url for user
            if ($form_data['status'] == 'incomplete') {
                if (! empty($request->get('token'))) {
                    $form_data['token'] = $existing_token_form_data['token'];
                } else {
                    $form_data['token'] = Str::random(4);
                }

                $post_submit_action['notification']['token'] = $form_data['token'];
                $post_submit_action['notification']['form_editable_url'] = action([\App\Http\Controllers\FormController::class, 'show'], ['form' => $form->slug ?: $form_id]).'?token='.$form_data['token'];
            } else {
                //if form submission status:complete, set token as null & get form view url
                $post_submit_action['notification']['view_form_url'] = action([\App\Http\Controllers\FormController::class, 'show'], ['form' => $form->slug ?: $form_id]);
                $post_submit_action['notification']['qr_code_text'] = $this->_generateQrCodeText($form->schema, $form_data['data']);
                $form_data['token'] = null;
            }

            //generate form submission reference number if enabled
            $form_data['submission_ref'] = null;
            if ((isset($form->schema['settings']['form_submision_ref']['is_enabled']) && $form->schema['settings']['form_submision_ref']['is_enabled']) && $form_data['status'] == 'complete') {
                $start_no = $form->schema['settings']['form_submision_ref']['start_no'];
                $min_digit = $form->schema['settings']['form_submision_ref']['min_digit'];

                $new_count = $form->submission_count == 0 ? $start_no : ($form->submission_count + $start_no);

                $number = str_pad($new_count, $min_digit, '0', STR_PAD_LEFT);

                $form_data['submission_ref'] = $number;

                if (isset($form->schema['settings']['form_submision_ref']['prefix'])) {
                    $form_data['submission_ref'] = $form->schema['settings']['form_submision_ref']['prefix'].$form_data['submission_ref'];
                }

                if (isset($form->schema['settings']['form_submision_ref']['suffix'])) {
                    $form_data['submission_ref'] = $form_data['submission_ref'].$form->schema['settings']['form_submision_ref']['suffix'];
                }

                $form_data['data']['submission_ref'] = $form_data['submission_ref'];
            }

            //if token exist update the submitted data
            if (! empty($request->get('token'))) {
                $submission = FormData::where('form_id', $form->id)
                    ->where('token', $request->get('token'))
                    ->findOrFail($request->get('form_data_id'));

                $submission->data = $form_data['data'];
                $submission->submission_ref = $form_data['submission_ref'];
                $submission->source = $form_data['source'];
                $submission->status = $form_data['status'];
                $submission->token = $form_data['token'];
                $submission->save();
            } else {
                //store form data.
                $submission = $form->data()->create($form_data);
            }

            //if submission is draft(incomplete) then return form_data_id & saved draft msg
            if (isset($form_data['data']['status']) && $form_data['data']['status'] == 'incomplete') {
                $post_submit_action['notification']['form_data_id'] = ! empty($request->get('form_data_id')) ? $request->get('form_data_id') : $submission->id;
                $post_submit_action['notification']['success_msg'] = __('messages.draft_saved');
            }

            //update form submission count if submission is complete
            if ((isset($form->schema['settings']['form_submision_ref']['is_enabled']) && $form->schema['settings']['form_submision_ref']['is_enabled']) && $form_data['status'] == 'complete') {
                $form->submission_count += 1;
                $form->save();
            }

            //check for demo environment & form is complete or not
            if (! $this->isDemo() && $form_data['status'] == 'complete') {
                //Send notification for form
                $emailConfig = $form->schema['emailConfig'];

                //check if attachment is enabled
                $attachments = [];
                $signature_attachments = [];
                foreach ($form->schema['form'] as $element) {
                    if ($element['type'] == 'file_upload' && $element['send_as_email_attachment'] == 1) {
                        $attachments[] = $element['name'];
                    }

                    //check if any signature field available to attach
                    if ($element['type'] == 'signature') {
                        $signature_attachments[] = [
                            'field_name' => $element['name'],
                            'label' => $element['label'],
                            'base_64_uri' => '',
                        ];
                    }
                }

                //get signature attachments
                if (! empty($signature_attachments)) {
                    foreach ($signature_attachments as $index => $signature) {
                        $signature_attachments[$index]['base_64_uri'] = $form_data['data'][$signature['field_name']];
                    }
                    $emailConfig['email']['signature_attachments'] = $signature_attachments;
                }

                if ((isset($emailConfig['email']['attach_pdf']) && $emailConfig['email']['attach_pdf']) || (isset($emailConfig['auto_response']['attach_pdf']) && $emailConfig['auto_response']['attach_pdf'])) {
                    $id = $submission->id;
                    $pdf = $this->__generatePdf($id);
                    $pdf_name = Str::slug($form->name, '-').'.pdf';
                }

                //Set user defined SMTP : use_system_smtp = User SMTP
                if (! empty($emailConfig['email']['enable']) && $emailConfig['smtp']['use_system_smtp']) {
                    //User SMTP
                    $form = Form::with('createdBy')->findOrFail($form_id);
                    $smtp = $form->createdBy->settings['smtp'];

                    config([
                        'mail.mailers.smtp.host' => $smtp['MAIL_HOST'],
                        'mail.mailers.smtp.port' => $smtp['MAIL_PORT'],
                        'mail.from.address' => $smtp['MAIL_FROM_ADDRESS'],
                        'mail.from.name' => $smtp['MAIL_FROM_NAME'],
                        'mail.mailers.smtp.encryption' => $smtp['MAIL_ENCRYPTION'],
                        'mail.mailers.smtp.username' => $smtp['MAIL_USERNAME'],
                        'mail.mailers.smtp.password' => $smtp['MAIL_PASSWORD']
                    ]);
                } else {
                    config([
                        'mail.mailers.smtp.host' => $emailConfig['smtp']['host'],
                        'mail.mailers.smtp.port' => $emailConfig['smtp']['port'],
                        'mail.from.address' => $emailConfig['smtp']['from_address'],
                        'mail.from.name' => $emailConfig['smtp']['from_name'],
                        'mail.mailers.smtp.encryption' => $emailConfig['smtp']['encryption'],
                        'mail.mailers.smtp.username' => $emailConfig['smtp']['username'],
                        'mail.mailers.smtp.password' => $emailConfig['smtp']['password']
                    ]);
                }

                //Form submission Notification
                if (! empty($emailConfig['email']['enable'])) {

                    //Replace the tags with values.
                    $temp = $this->_replaceTags(
                        $form_data['data'],
                        ['subject' => $emailConfig['email']['subject'],
                            'body' => $emailConfig['email']['body'], ],
                        $form['schema']['form']
                    );
                    $emailConfig['email']['subject'] = $temp['subject'];
                    $emailConfig['email']['body'] = $temp['body'];

                    //Attachments
                    if (! empty($attachments)) {
                        $emailConfig['email']['attachment'] = $this->getAttachments($attachments, $form_data['data']);
                    }

                    if ((isset($emailConfig['email']['attach_pdf']) && $emailConfig['email']['attach_pdf'])) {
                        $emailConfig['email']['pdf_attachment'] = $pdf;
                        $emailConfig['email']['pdf_name'] = $pdf_name;
                    }

                    if (
                        isset($emailConfig['email']['reply_to_email']) &&
                        ! empty($emailConfig['email']['reply_to_email']) &&
                        ! empty($form_data['data'][$emailConfig['email']['reply_to_email']])
                    ) {
                        $emailConfig['email']['reply_to'] = $form_data['data'][$emailConfig['email']['reply_to_email']];
                    }

                    //get barcode & qr code attachment
                    if (
                        isset($form_data['data']['submission_ref']) &&
                        ! empty($form_data['data']['submission_ref'])
                    ) {
                        $ref_num = $form_data['data']['submission_ref'];
                        $emailConfig['email']['barcode'][$ref_num.'_barcode.png'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'bar_code');
                        $emailConfig['email']['barcode'][$ref_num.'_qrcode.png'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'qr_code');
                    }
                    Mail::send(new FormSubmitted($emailConfig['email']));
                }

                //Auto-Response
                if ($emailConfig['auto_response']['is_enable']) {

                    //Replace the tags with values.
                    $temp = $this->_replaceTags(
                        $form_data['data'],
                        ['subject' => $emailConfig['auto_response']['subject'],
                            'body' => $emailConfig['auto_response']['body'], ],
                        $form['schema']['form']
                    );
                    $emailConfig['auto_response']['subject'] = $temp['subject'];
                    $emailConfig['auto_response']['body'] = $temp['body'];

                    //"TO" field is dynamic input value.
                    $emailConfig['auto_response']['to'] = isset($form_data['data'][$emailConfig['auto_response']['to']]) ? $form_data['data'][$emailConfig['auto_response']['to']] : null;

                    if (! empty($attachments)) {
                        $emailConfig['auto_response']['attachment'] = $this->getAttachments($attachments, $form_data['data']);
                    }

                    //get signature attachments
                    if (! empty($signature_attachments)) {
                        foreach ($signature_attachments as $index => $signature) {
                            $signature_attachments[$index]['base_64_uri'] = $form_data['data'][$signature['field_name']];
                        }
                        $emailConfig['auto_response']['signature_attachments'] = $signature_attachments;
                    }

                    if ((isset($emailConfig['auto_response']['attach_pdf']) && $emailConfig['auto_response']['attach_pdf'])) {
                        $emailConfig['auto_response']['pdf_attachment'] = $pdf;
                        $emailConfig['auto_response']['pdf_name'] = $pdf_name;
                    }

                    //get barcode & qr code attachment for response
                    if (
                        isset($form_data['data']['submission_ref']) &&
                        ! empty($form_data['data']['submission_ref'])
                    ) {
                        $ref_num = $form_data['data']['submission_ref'];
                        $emailConfig['auto_response']['barcode'][$ref_num.'_barcode.png'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'bar_code');
                        $emailConfig['auto_response']['barcode'][$ref_num.'_qrcode.png'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'qr_code');
                    }

                    if (! empty($emailConfig['auto_response']['to'])) {
                        Mail::send(new FormSubmitted($emailConfig['auto_response']));
                    }
                }

                //Send data to mailchimp if enabled.
                if (! empty($form->mailchimp_details['is_enable']) && $form->mailchimp_details['is_enable'] == 1) {

                    //Set config details.
                    config(['newsletter.apiKey' => $form->mailchimp_details['api_key']]);
                    config(['newsletter.lists.subscribers.id' => $form->mailchimp_details['list_id']]);

                    //Subscribe if email is set.
                    if (isset($form_data['data'][$form->mailchimp_details['email_field']]) && ! empty($form_data['data'][$form->mailchimp_details['email_field']])) {

                        //Get dynamic field from form input.
                        $email = $form_data['data'][$form->mailchimp_details['email_field']];

                        //explode name to get first & last name
                        $name = explode(' ', $form_data['data'][$form->mailchimp_details['name_field']], 2);
                        $fname = $name[0];
                        $lname = ! empty($name[1]) ? $name[1] : '';
                        if ($form->mailchimp_details['status'] == 'subscribe') {
                            Newsletter::subscribe($email, ['FNAME' => $fname, 'LNAME' => $lname]);
                        } elseif ($form->mailchimp_details['status'] == 'subscribe_pending') {
                            Newsletter::subscribePending($email, ['FNAME' => $fname, 'LNAME' => $lname]);
                        }
                    }
                }

                $this->__addSubscriberInAcelleMail($form, $form_data['data']);
            }
            $this->_sendWebhook($submission);
            //get qr & bar code for submission ref
            $data['sub_ref_qr_code'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'qr_code');
            $data['sub_ref_bar_code'] = $this->_generateSubmissionRefBarCode($form->schema, $form_data['data'], 'bar_code');
            $post_submit_action['notification']['submission_msg'] = $this->_generateSubmissionMsgHtml($form, $data);

            return $this->respondSuccess($message = null, $post_submit_action);
        } catch (\Exception $e) {
            return $this->respondWentWrong($e);
        }
    }

    private function __addSubscriberInAcelleMail($form, $data)
    {
        $acelle_mail_info = $form->acelle_mail_info;
        if (
            ! empty($acelle_mail_info) &&
            $acelle_mail_info['is_enable'] &&
            ! empty($acelle_mail_info['api_token']) &&
            ! empty($acelle_mail_info['list_id']) &&
            ! empty($acelle_mail_info['campaign_fields'])
        ) {
            $args = [
                'api_token' => trim($acelle_mail_info['api_token']),
                'list_uid' => $acelle_mail_info['list_id'],
            ];

            foreach ($acelle_mail_info['campaign_fields'] as $field) {
                if (! empty($field['key']) && ! empty($field['param_field_name']) && ! empty($data[$field['param_field_name']])) {
                    $args[$field['key']] = is_array($data[$field['param_field_name']]) ? implode(', ', $data[$field['param_field_name']]) : strip_tags($data[$field['param_field_name']]);
                }
            }

            $request_uri = config('constants.ACELLE_MAIL_API').'/subscribers'.'?'.http_build_query($args);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $request_uri);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);
        }
    }

    protected function _sendWebhook($submission)
    {
        $webhook = $submission->form->webhook_info;

        if (
            isset($webhook['is_enable']) &&
            $webhook['is_enable'] &&
            ! empty($webhook['url']) &&
            ! empty($webhook['secret_key'])
        ) {

            //get playload to send
            $payload = $submission->toArray();
            //unset unused data
            unset($payload['submitted_by'], $payload['form'], $payload['token']);

            WebhookCall::create()
                ->url($webhook['url'])
                ->useSecret($webhook['secret_key'])
                ->timeoutInSeconds(5)
                ->maximumTries(5)
                ->doNotVerifySsl()
                ->payload($payload)
                ->dispatchSync();
        }
    }

    protected function _getBarCodeForRefNum($ref_num = '', $type = 'qr_code', $is_img_format = false)
    {
        if (! empty($ref_num) && in_array($type, ['bar_code'])) {
            $bar_code = (string)Image::canvas(305,150,"#fff")->insert(base64_decode(DNS1D::getBarcodePNG($ref_num, 'C128', 2.5,100,array(27,41,75), true)), 'center')->encode('data-url');

            return $is_img_format ? '<img src="'.$bar_code.'" alt="barcode"/>' : $bar_code;
        }

        if (! empty($ref_num) && in_array($type, ['qr_code'])) {
            $qr_code = (string)Image::canvas(300,300,"#fff")->insert(base64_decode(DNS2D::getBarcodePNG($ref_num, 'QRCODE', 12,12,array(27,41,75), true)), 'center')->encode('data-url');

            return $is_img_format ? '<img src="'.$qr_code.'" alt="qrcode"/>' : $qr_code;
        }

        return '';
    }

    protected function _replaceTags($form_data, $strings, $form_schema)
    {
        $ref_num = $form_data['submission_ref'] ?? '';
        $ref_num_qr_code = $this->_getBarCodeForRefNum($ref_num, 'qr_code', true);
        $ref_num_bar_code = $this->_getBarCodeForRefNum($ref_num, 'bar_code', true);
        foreach ($form_data as $name => $value) {
            foreach ($strings as $key => $string) {
                //If value is array(like for multiselect or checkbox) then implode it.
                $value = is_array($value) ? implode(',', $value) : $value;
                $string = str_replace('__'.$name.'__', $value, $string);

                //replace qr/bar code
                $string = str_replace('__submission_ref_qr_code__', $ref_num_qr_code, $string);
                $string = str_replace('__submission_ref_bar_code__', $ref_num_bar_code, $string);
                $strings[$key] = $string;
            }
        }

        return $strings;
    }

    public function show($form_id, Request $request)
    {
        $user_id = $request->user()->id;

        $form = Form::findOrFail($form_id);
        $data = FormData::where('form_id', $form_id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        //check permission if user is not a creator
        $has_permission = ($form->created_by != $user_id) ? $this->doUserHavePermission($form->id, 'can_view_data') : true;
        if (! $has_permission) {
            abort(404);
        }

        return view('form_data.show')
            ->with(compact('form', 'data'));
    }

    public function viewData($id)
    {
        if (request()->ajax()) {
            $form_data = FormData::with(['form', 'submittedBy',
                'comments' => function ($q) {
                    $q->latest();
                },
                'comments.commentedBy', ])->findOrFail($id);

            return view('form_data.view_form_data')
                ->with(compact('form_data'));
        }
    }

    public function destroy($id)
    {
        try {
            if (request()->ajax()) {
                $form_data = FormData::findOrFail($id);
                $form_data->delete();
                $form_data->comments()->delete();
            }

            return $this->respondSuccess(__('messages.deleted_successfully'));
        } catch (Exception $e) {
            return $this->respondWentWrong($e);
        }
    }

    public function getAttachments($attachment_fields, $form_data)
    {
        $attachments = [];
        foreach ($attachment_fields as $attachment_field) {
            foreach ($form_data as $key => $values) {
                if ($attachment_field == $key) {
                    foreach ($values as $key => $value) {
                        $uploaded_file = explode(',', $value);
                    }
                    $attachments = array_merge($attachments, $uploaded_file);
                }
            }
        }

        return $attachments;
    }

    /**
     * return the data to display
     * a report
     */
    public function getReport($id)
    {
        $user = request()->user();
        $form = Form::with('data')
                ->findOrFail($id);

        //check permission if user is not a creator
        $has_permission = ($form->created_by != $user->id) ? $this->doUserHavePermission($form->id, 'can_view_data') : true;
        if (! $has_permission) {
            abort(404);
        }

        $charts = [];
        if (isset($form->schema['form'])) {
            foreach ($form->schema['form'] as $element) {
                if (in_array($element['type'], ['dropdown', 'radio', 'checkbox', 'rating'])) {
                    //chart title
                    $charts[$element['name']]['name'] = $element['label'];

                    //getting given option
                    if ($element['type'] == 'rating') {
                        $all_rating = implode(',', range($element['min_rating'], $element['max_rating'], $element['increment']));
                        $options = explode(',', $all_rating);
                    } else {
                        $options = explode(PHP_EOL, $element['options']);
                    }

                    $dropdowns = [];
                    foreach ($options as $key => $value) {
                        $dropdowns[$value] = 0;
                    }

                    //generating report
                    foreach ($options as $option) {
                        foreach ($form->data as $submitted_data) {
                            if (isset($submitted_data['data'][$element['name']]) && ($submitted_data['data'][$element['name']] == $option)) {
                                $dropdowns[$option] += 1;
                            } elseif (isset($submitted_data['data'][$element['name']]) && is_array($submitted_data['data'][$element['name']]) && in_array($option, $submitted_data['data'][$element['name']])) {
                                $dropdowns[$option] += 1;
                            }
                        }
                    }

                    //storing report
                    $charts[$element['name']]['values'] = $dropdowns;
                }
            }
        }

        $visitors_chart = $this->_generateVisiorsLineChartData($form->id);
        $referrers_chart = $this->_generateVisiorsPieChartData($form->id);

        return view('form_data.report')
            ->with(compact('charts', 'form', 'visitors_chart', 'referrers_chart'));
    }

    private function __getVisitorsReport($form_id, $chart_type)
    {
        $query = Visitor::where('form_id', $form_id)
                        ->whereBetween(DB::raw('date(created_at)'), [Carbon::now()->subDays(30), Carbon::now()])
                        ->select(
                            DB::raw('count(form_id) as total_visits'),
                            DB::raw('SUM(IF(is_unique = 1,1,0)) as unique_visits')
                        );

        if ($chart_type == 'line') {
            $query->addSelect(DB::raw('Date(created_at) as date'))
                ->groupBy(DB::raw('Date(created_at)'));
        } elseif ($chart_type == 'pie') {
            $query->addSelect('referrer')
                ->groupBy('referrer');
        }

        $visitors = $query->get();

        return $visitors;
    }

    protected function _generateVisiorsLineChartData($form_id)
    {
        $visitors = $this->__getVisitorsReport($form_id, 'line');
        //generate all the labels between 30 days
        $dates = [];
        $labels = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;
            $labels[] = date('j M Y', strtotime($date));
        }

        //get total & unique visits for last 30 days
        $total_visits_in_last_30_days = [];
        $unique_visits_in_last_30_days = [];
        foreach ($dates as $key => $date) {
            $visitor_date = null;
            $total_visits = 0;
            $unique_visits = 0;
            foreach ($visitors as $key => $visitor) {
                if ($visitor['date'] == $date) {
                    $visitor_date = $visitor['date'];
                    $total_visits = $visitor['total_visits'];
                    $unique_visits = $visitor['unique_visits'];
                    break;
                }
            }
            //if date match store values
            if ($visitor_date == $date) {
                $total_visits_in_last_30_days[] = (float) $total_visits;
                $unique_visits_in_last_30_days[] = (float) $unique_visits;
            } else {
                $total_visits_in_last_30_days[] = 0;
                $unique_visits_in_last_30_days[] = 0;
            }
        }

        $charts_data = [
            'title' => __('messages.total_visitors_in_last_30_days'),
            'labels' => $labels,
            'total_visits_label' => __('messages.total_visits'),
            'total_visits' => $total_visits_in_last_30_days,
            'unique_visits_label' => __('messages.unique_visits'),
            'unique_visits' => $unique_visits_in_last_30_days,
        ];

        return $charts_data;
    }

    public function _generateVisiorsPieChartData($form_id)
    {
        $visitors = $this->__getVisitorsReport($form_id, 'pie');

        //get referrer as key value
        $referrers = [];
        foreach ($visitors as $key => $visitor) {
            if (! empty($visitor->referrer)) {
                $referrers[] = ['name' => $visitor->referrer, 'y' => $visitor->total_visits];
            } else {
                $referrers[] = ['name' => __('messages.direct_visits'), 'y' => $visitor->total_visits];
            }
        }

        $charts_data = [
            'name' => __('messages.referrers_in_last_30_days'),
            'values' => $referrers,
        ];

        return $charts_data;
    }

    private function __generatePdf($id)
    {
        $form_data = FormData::with(['form', 'submittedBy'])->findOrFail($id);

        $pdf_header = $this->__replacePdfTags($form_data);

        $pdf = PDF::loadView('form_data.partials.form_data_pdf',
            ['form_data' => $form_data, 'pdf_header' => $pdf_header]
        );

        return $pdf;
    }

    public function downloadPdf($id)
    {
        $formData = FormData::with(['form'])->find($id);

        if (! (auth()->user()->can('superadmin') || $this->checkIfUserIsCreatorOfGivenForm($formData->form->id) || $this->doUserHavePermission($formData->form->id, 'can_view_data'))) {
            abort(403, 'Unauthorized action.');
        }

        $pdf = $this->__generatePdf($id);

        return $pdf->stream('document.pdf');
    }

    public function getEditformData($id_or_slug, $data_id)
    {
        $form = Form::where('id', $id_or_slug)
                ->orWhere('slug', $id_or_slug)
                ->first();

        if (empty($form)) {
            abort(404);
        }

        //if submitted data id available get submitted data
        if (! empty($data_id)) {
            $form->load(['data' => function ($query) use ($data_id, $form) {
                $query->where('id', $data_id)
                ->where('form_id', $form->id);
            }]);
        }

        $nav = false;
        $action_by = 'admin';

        return view('form.show')
            ->with(compact('form', 'nav', 'action_by'));
    }

    public function postEditformData($form_id, $data_id)
    {
        try {
            $form = Form::findOrFail($form_id);
            $form_data = [];
            parse_str(request()->get('form_data'), $form_data['data']);

            if (! empty($data_id)) {
                $existing_data = FormData::where('form_id', $form->id)
                                    ->findOrFail($data_id);

                $existing_data->data = $form_data['data'];
                $existing_data->save();
            }
            $post_submit_action['notification'] = $form->schema['settings']['notification'];
            $post_submit_action['notification']['success_msg'] = __('messages.success');
            $post_submit_action['notification']['post_submit_action'] = 'redirect';
            $post_submit_action['notification']['redirect_url'] = action([\App\Http\Controllers\FormDataController::class, 'show'], ['id' => $form->id]);

            return $this->respondSuccess(null, $post_submit_action);
        } catch (Exception $e) {
            return $this->respondWentWrong($e);
        }
    }

    private function __replacePdfTags($form_data)
    {
        $header = '';
        if (
            isset($form_data->form['schema']['settings']['pdf_design']) &&
            ! empty($form_data->form['schema']['settings']['pdf_design']['header'])
        ) {
            $form_name = $form_data->form->name;
            $submission_date = Carbon::parse($form_data->created_at)->format(config('constants.APP_DATE_FORMAT', 'Y-m-d'));
            $replacable_tags = $form_data->form['schema']['settings']['pdf_design']['header'];
            $header = preg_replace(['/{form_name}/', '/{submission_date}/'], [$form_name, $submission_date], $replacable_tags);
        }

        return $header;
    }

    protected function _generateSubmissionMsgHtml($form, $data)
    {
        $submission_msg = View::make('form_data.partials.submission_msg')
                            ->with(compact('form', 'data'))
                            ->render();

        return $submission_msg;
    }

    protected function _generateQrCodeText($schema, $form_data)
    {
        $string = '';
        $array = [];
        if (
            ! in_array($schema['settings']['notification']['post_submit_action'], ['redirect']) &&
            isset($schema['settings']['is_qr_code_enabled']) &&
            $schema['settings']['is_qr_code_enabled']
        ) {
            foreach ($schema['form'] as $key => $element) {
                if (
                    ! in_array($element['type'], ['heading', 'html_text', 'hr', 'signature', 'file_upload'])
                ) {
                    if (
                        isset($form_data[$element['name']]) &&
                        ! is_array($form_data[$element['name']]) &&
                        ! empty($form_data[$element['name']])
                    ) {
                        //check data format & set data
                        if (
                            isset($schema['settings']['qr_code_data_format']) &&
                            in_array($schema['settings']['qr_code_data_format'], ['json'])
                        ) {
                            $array[$element['label']] = strip_tags($form_data[$element['name']]);
                        } else {
                            $string .= $element['label'].': '.strip_tags($form_data[$element['name']]).', ';
                        }
                    } elseif (
                        isset($form_data[$element['name']]) &&
                        is_array($form_data[$element['name']]) &&
                        ! empty($form_data[$element['name']])
                    ) {
                        //check data format & set data
                        if (
                            isset($schema['settings']['qr_code_data_format']) &&
                            in_array($schema['settings']['qr_code_data_format'], ['json'])
                        ) {
                            $array[$element['label']] = implode(', ', $form_data[$element['name']]);
                        } else {
                            $string .= $element['label'].': '.implode(', ', $form_data[$element['name']]).', ';
                        }
                    }
                }

                if (
                    in_array($element['type'], ['file_upload']) &&
                    isset($form_data[$element['name']]) &&
                    ! empty($form_data[$element['name']])
                ) {
                    //convert file name to an array
                    $file = implode($form_data[$element['name']]);
                    $files = explode(',', $file);

                    $file_urls = [];
                    foreach ($files as $key => $value) {
                        $file_urls[] = \Storage::url(config('constants.doc_path').'/'.$value);
                    }

                    //check data format & set data
                    if (
                        isset($schema['settings']['qr_code_data_format']) &&
                        in_array($schema['settings']['qr_code_data_format'], ['json'])
                    ) {
                        $array[$element['label']] = implode(', ', $file_urls);
                    } else {
                        $string .= $element['label'].': '.implode(', ', $file_urls).', ';
                    }
                }
            }
        }

        if (
            isset($schema['settings']['qr_code_data_format']) &&
            in_array($schema['settings']['qr_code_data_format'], ['json'])
        ) {
            return json_encode($array);
        } else {
            return $string;
        }
    }

    protected function _generateSubmissionRefBarCode($schema, $data, $type = 'bar_code')
    {
        if (
            in_array($type, ['bar_code']) &&
            isset($schema['settings']['is_ref_num_bar_code_enabled']) &&
            $schema['settings']['is_ref_num_bar_code_enabled']
        ) {
            $ref_num = $data['submission_ref'] ?? '';

            return $this->_getBarCodeForRefNum($ref_num, $type);
        }

        if (
            in_array($type, ['qr_code']) &&
            isset($schema['settings']['is_ref_num_qr_code_enabled']) &&
            $schema['settings']['is_ref_num_qr_code_enabled']
        ) {
            $ref_num = $data['submission_ref'] ?? '';

            return $this->_getBarCodeForRefNum($ref_num, $type);
        }
    }
}
