<?php

namespace App\Http\Controllers\V1\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPaymentRequest;
use App\Models\Payment;
use App\Models\EmailLog;
use Vinkla\Hashids\Facades\Hashids;

class PublishPaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(SendPaymentRequest $request, Payment $payment)
    {
        $this->authorize('send payment', $payment);

        $data = $payment->sendPaymentData($request->all());

        $log = EmailLog::create([
            'from' => $data['from'],
            'to' => $data['to'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'mailable_type' => Payment::class,
            'mailable_id' => $data['payment']['id'],
        ]);

        $log->token = Hashids::connection(EmailLog::class)->encode($log->id);
        $log->save();

        $url = route('payment', ['email_log' => $log->token]);

        return response()->json([
            'url' => $url
        ]);
    }
}
