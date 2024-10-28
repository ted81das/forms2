<?php

namespace App\Http\Controllers;

use App\Form;
use App\Mail\TestEmail;
use App\PackageSubscription;
use App\UserForm;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = request()->user();

        if (request()->ajax()) {
            $subscription = PackageSubscription::activeSubscription($user->id);

            $forms = Form::leftJoin('form_data', 'forms.id', '=', 'form_data.form_id')
                        ->select('name', 'slug', 'description', 'forms.created_at', 'forms.id', DB::raw('COUNT(form_data.form_id) as data_count'), 'schema')
                        ->where('is_template', 0)
                        ->where('created_by', $user->id)
                        ->groupBy('id');

            return DataTables::of($forms)
                    ->addColumn(
                        'action',
                        function (Form $form) use ($subscription, $user) {
                            $action = '';

                            if (! empty($form->schema)) {
                                $action = '<a href="'.action([\App\Http\Controllers\FormController::class, 'show'], ['form' => $form->slug ?: $form->id]).'"'.'
                                target="_blank" 
                                class="btn btn-sm btn-info  m-1" data-toggle="tooltip" title="'.__('messages.view').'">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>';
                            }

                            $action .= '<a href="'.action([\App\Http\Controllers\FormController::class, 'edit'], ['form' => $form->id]).'"'.' 
                                class="btn btn-sm btn-warning  m-1" data-toggle="tooltip" title="'.__('messages.edit').'">
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </a>';

                            $action .= '<button type="button" data-href="'.action([\App\Http\Controllers\FormController::class, 'destroy'], ['form' => $form->id]).'"'.' class="btn btn-sm btn-danger delete_form m-1" data-toggle="tooltip" 
                                title="'.__('messages.delete').'">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>';

                            $action .= '<button type="button" data-href="'.action([\App\Http\Controllers\FormController::class, 'copyForm'], ['id' => $form->id]).'"'.' class="btn btn-sm btn-primary copy_form m-1" data-toggle="tooltip" 
                                title="'.__('messages.copy_this_form').'">
                                    <i class="fas fa-copy"></i>
                                </button>';

                            $action .= '<button type="button" data-href="'.action([\App\Http\Controllers\FormController::class, 'generateWidget'], ['id' => $form->id]).'"'.' class="btn btn-sm btn-info generate_widget m-1" data-toggle="tooltip" 
                                title="'.__('messages.widget').'">
                                    <i class="fa fa-random" aria-hidden="true"></i>
                                </button>';

                            $action .= '<a href="'.action([\App\Http\Controllers\FormDataController::class, 'show'], ['id' => $form->id]).'"'.'"
                                target="_blank" 
                                class="btn btn-sm btn-success  m-1" data-toggle="tooltip" title="'.__('messages.view_form_data').'">
                                    <i class="fa fa-list" aria-hidden="true"></i>
                                </a>';

                            $superadmins = env('SUPERADMIN_EMAILS');
                            $superadmin_emails = explode(',', $superadmins);
                            if (in_array($user->email, $superadmin_emails) ||
                                (is_saas_enabled() && (isset($subscription->package_details['is_form_downloadable']) && $subscription->package_details['is_form_downloadable'])) || ! is_saas_enabled()) {
                                $action .= '<a href="'.action([\App\Http\Controllers\FormController::class, 'downloadCode'], ['id' => $form->id]).'"'.'" class="btn btn-sm btn-dark m-1" data-toggle="tooltip" 
                                title="'.__('messages.download_code').'">
                                    <i class="fas fa-download" aria-hidden="true"></i>
                                </a>';
                            }

                            $action .= '<a href="'.action([\App\Http\Controllers\FormDataController::class, 'getReport'], ['id' => $form->id]).'"'.'"
                                target="_blank" 
                                class="btn btn-sm btn-success  m-1" data-toggle="tooltip" title="'.__('messages.report').'">
                                    <i class="fas fa-chart-pie" aria-hidden="true"></i>
                                </a>';

                            $action .= '<a data-href="'.action([\App\Http\Controllers\FormController::class, 'getCollab'], ['id' => $form->id]).'"'.'class="btn btn-sm btn-primary  m-1 collab_btn" data-toggle="tooltip" title="'.__('messages.collaborate').'">
                                    <i class="fas fa-handshake text-white" aria-hidden="true"></i>
                                </a>';

                            return $action;
                        }
                    )
                    ->editColumn('created_at', function ($row) {
                        $date_format = config('constants.APP_DATE_FORMAT');
                        if (config('constants.APP_TIME_FORMAT') == '12') {
                            $date_format .= ' h:i A';
                        } elseif (config('constants.APP_TIME_FORMAT') == '24') {
                            $date_format .= ' H:i';
                        } else {
                            $date_format = 'm/d/Y h:i A';
                        }

                        return ! empty($row->created_at) ? Carbon::createFromTimestamp(strtotime($row->created_at))->format($date_format) : null;
                    })
                    ->editColumn('data_count', function ($row) {
                        return $row->data_count;
                    })
                    ->editColumn('name', function ($row) {
                        $html = $row->name;
                        if (empty($row->schema)) {
                            $html .= '<br><small class="text-danger">('.(__('messages.form_is_incomplete')).')</small>';
                        }

                        return $html;
                    })
                    ->removeColumn('id')
                    ->rawColumns(['action', 'created_at', 'data_count', 'name'])
                    ->make(true);
        }

        //Count forms
        $form_count = Form::where('created_by', $user->id)
                            ->where('is_template', 0)
                            ->count();

        //Count templates.
        $template_count = Form::where('created_by', $user->id)
                        ->where('is_template', 1)
                        ->count();

        //Count submissions.
        $submission_count = Form::join('form_data as fd', 'forms.id', '=', 'fd.form_id')
                    ->where('is_template', 0)
                    ->where('created_by', $user->id)
                    ->count();

        return view('home')
            ->with(compact('form_count', 'template_count', 'submission_count'));
    }

    /**
     * Show Form Template
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTemplate()
    {
        if (request()->ajax()) {
            $user_id = request()->user()->id;

            $forms = Form::select('name', 'description', 'id', 'slug', 'is_global_template')
                        ->where(function ($query) use($user_id) {
                            $query->where('is_template', 1)
                                ->where('created_by', $user_id)
                                ->orWhere('is_global_template', 1);
                        })
                        ->groupBy('id');

            return DataTables::of($forms)
                    ->addColumn('action', function ($row) {
                        $action = '<a href="'.action([\App\Http\Controllers\FormController::class, 'show'], ['form' => $row->slug ?: $row->id]).'"'.'
                                target="_blank" 
                                class="btn btn-sm btn-info  m-1" data-toggle="tooltip" title="'.__('messages.view').'">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>';

                        if (! $row->is_global_template || auth()->user()->can('superadmin')) {
                            $action .= '<a href="'.action([\App\Http\Controllers\FormController::class, 'edit'], ['form' => $row->id]).'"'.' 
                                class="btn btn-sm btn-warning  m-1" data-toggle="tooltip" title="'.__('messages.edit').'">
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </a>
                                <button type="button" data-href="'.action([\App\Http\Controllers\FormController::class, 'destroy'], ['form' => $row->id]).'"'.' class="btn btn-sm btn-danger delete_template m-1" data-toggle="tooltip" 
                                    title="'.__('messages.delete').'">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>';
                        }

                        return $action;
                    })
                    ->editColumn('is_global_template', function ($row) {
                        if (auth()->user()->can('superadmin')) {
                            $checked = $row->is_global_template ? 'checked' : '';
                            $html = '<div class="form-check">
                                        <input class="form-check-input toggle_global_template" type="checkbox" value="1" '.$checked.' data-form_id="'.$row->id.'">
                                    </div>';

                            return $html;
                        }
                    })
                    ->editColumn('name', function ($row) {
                        $name = $row->name;

                        if ($row->is_global_template) {
                            $name .= '<br><span class="badge badge-pill badge-info">'.__('messages.pre_made').'</span>';
                        }

                        return $name;
                    })
                    ->removeColumn('id')
                    ->rawColumns(['action', 'is_global_template', 'name'])
                    ->make(true);
        }

        return view('home');
    }

    /**
     * Tests if SMTP connection details is correct or not.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function testSMTP()
    {
        try {
            //Set the default config.
            config([
                'mail.mailers.smtp.host' => request()->host,
                'mail.mailers.smtp.port' => request()->port,
                'mail.from.address' => request()->from_address,
                'mail.from.name' => request()->from_name,
                'mail.mailers.smtp.encryption' => request()->encryption,
                'mail.mailers.smtp.username' => request()->username,
                'mail.mailers.smtp.password' => request()->password
            ]);

            Mail::to(request()->from_address)
            ->send(new TestEmail());

            return $this->respondSuccess();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    /**
     * Show assigned forms
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAssignedForms(Request $request)
    {
        if ($request->ajax()) {
            $forms = UserForm::join('forms', 'user_forms.form_id', '=', 'forms.id')
                        ->leftJoin('users', 'forms.created_by', '=', 'users.id')
                        ->where('user_forms.assigned_to', \Auth::id())
                        ->select('user_forms.permissions as permissions', 'forms.name as name', 'forms.description as description', 'forms.id as form_id', 'forms.created_at as created_at', 'forms.slug as slug', 'users.name as created_by');

            return DataTables::of($forms)
                    ->addColumn(
                        'action',
                        function ($row) {
                            $action = '';
                            if (! empty($row->permissions) && in_array('can_view_form', $row->permissions)) {
                                $action = '<a href="'.action([\App\Http\Controllers\FormController::class, 'show'], ['form' => $row->slug ?: $row->form_id]).'"'.'
                                target="_blank" 
                                class="btn btn-sm btn-info  m-1" data-toggle="tooltip" title="'.__('messages.view').'">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>';
                            }

                            if (! empty($row->permissions) && in_array('can_design_form', $row->permissions)) {
                                $action .= '<a href="'.action([\App\Http\Controllers\FormController::class, 'edit'], ['form' => $row->form_id]).'"'.' 
                                    class="btn btn-sm btn-warning  m-1" data-toggle="tooltip" title="'.__('messages.edit').'">
                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                    </a>';
                            }

                            if (! empty($row->permissions) && in_array('can_view_data', $row->permissions)) {
                                $action .= '<a href="'.action([\App\Http\Controllers\FormDataController::class, 'show'], ['id' => $row->form_id]).'"'.'"
                                target="_blank" 
                                class="btn btn-sm btn-success  m-1" data-toggle="tooltip" title="'.__('messages.view_form_data').'">
                                    <i class="fa fa-list" aria-hidden="true"></i>
                                </a>
                                <a href="'.action([\App\Http\Controllers\FormDataController::class, 'getReport'], ['id' => $row->form_id]).'"'.'"
                                target="_blank" 
                                class="btn btn-sm btn-success  m-1" data-toggle="tooltip" title="'.__('messages.report').'">
                                    <i class="fas fa-chart-pie" aria-hidden="true"></i>
                                </a>';
                            }

                            return $action;
                        }
                    )
                    ->editColumn('created_by', function ($row) {
                        return ucfirst($row->created_by);
                    })
                    ->removeColumn(['id', 'permissions'])
                    ->rawColumns(['action', 'created_by'])
                    ->make(true);
        }
    }
}
