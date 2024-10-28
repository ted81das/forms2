<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class FormSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->to(array_map('trim', explode(',', $this->data['to'])));

        $this->subject($this->data['subject'])
                ->view('emails.form_submitted')
                ->with(['body' => $this->data['body']]);

        if (! empty($this->data['cc'])) {
            $this->cc(array_map('trim', explode(',', $this->data['cc'])));
        }

        if (! empty($this->data['bcc'])) {
            $this->bcc(array_map('trim', explode(',', $this->data['bcc'])));
        }

        if (
            isset($this->data['reply_to']) &&
            ! empty($this->data['reply_to'])
        ) {
            $this->replyTo($this->data['reply_to']);
        }

        if (! empty($this->data['attachment'])) {
            foreach ($this->data['attachment'] as $key => $value) {
                if (! empty($value)) {
                    $name = explode('_', $value, 2);
                    $this->attach(public_path('uploads').'/'.config('constants.doc_path').'/'.$value, [
                        'as' => $name[1] ?? '',
                    ]);
                }
            }
        }

        if (! empty($this->data['pdf_attachment']) && ! empty($this->data['pdf_name'])) {
            $this->attachData($this->data['pdf_attachment']->Output(), $this->data['pdf_name'], [
                'mime' => 'application/pdf',
            ]);
        }

        //attach signature
        if (! empty($this->data['signature_attachments'])) {
            foreach ($this->data['signature_attachments'] as $key => $signature) {
                if (! empty($signature['base_64_uri'])) {
                    $this->attachData(base64_decode(str_replace('data:image/png;base64,', '', $signature['base_64_uri'])),
                        Str::slug($signature['label'], '-').'.png',
                        [
                            'mime' => 'image/png',
                        ]
                    );
                }
            }
        }

        //attach qr/bar code
        if (! empty($this->data['barcode'])) {
            foreach ($this->data['barcode'] as $key => $raw_attachment) {
                if (! empty($raw_attachment)) {
                    $this->attachData(base64_decode(str_replace('data:image/png;base64,', '', $raw_attachment)),
                        $key,
                        [
                            'mime' => 'image/png',
                        ]
                    );
                }
            }
        }

        return $this;
    }
}
