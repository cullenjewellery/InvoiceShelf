<?php

namespace App\Http\Controllers\V1\Admin\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendInvoiceRequest;
use App\Models\Invoice;
use App\Models\EmailLog;
use Vinkla\Hashids\Facades\Hashids;

class PublishInvoiceController extends Controller
{
    /**
     * Mail a specific invoice to the corresponding customer's email address.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(SendInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('send invoice', $invoice);

        $data = $invoice->sendInvoiceData($request->all());

        $log = EmailLog::create([
            'from' => $data['from'],
            'to' => $data['to'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'mailable_type' => Invoice::class,
            'mailable_id' => $data['invoice']['id'],
        ]);

        $log->token = Hashids::connection(EmailLog::class)->encode($log->id);
        $log->save();

        $url = route('invoice', ['email_log' => $log->token]);

        return response()->json([
            'url' => $url,
        ]);

    }
}
