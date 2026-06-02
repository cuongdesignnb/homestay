<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SePay\SePay\Datas\SePayWebhookData;
use SePay\SePay\Events\SePayWebhookEvent;
use SePay\SePay\Models\SePayTransaction;

class SePayWebhookController extends Controller
{
    /**
     * Handle SePay Webhook - Custom controller với logging chi tiết
     */
    public function webhook(Request $request)
    {
        // Log toàn bộ request để debug
        Log::channel('daily')->info('=== SEPAY WEBHOOK RECEIVED ===', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip(),
            'method' => $request->method(),
        ]);

        // Lấy token từ header
        $token = $this->getApiToken($request);
        $configToken = config('sepay.webhook_token');
        
        Log::channel('daily')->info('Token check', [
            'received_token' => $token ? substr($token, 0, 20) . '...' : 'NULL',
            'config_token' => $configToken ? substr($configToken, 0, 20) . '...' : 'NULL',
            'match' => $token === $configToken,
        ]);

        // Kiểm tra token
        if ($configToken && $token !== $configToken) {
            Log::channel('daily')->error('SEPAY WEBHOOK: Invalid token!');
            return response()->json(['error' => 'Invalid Token'], 401);
        }

        // Parse webhook data
        $sePayWebhookData = new SePayWebhookData(
            $request->integer('id'),
            $request->string('gateway')->value(),
            $request->string('transactionDate')->value(),
            $request->string('accountNumber')->value(),
            $request->string('subAccount')->value(),
            $request->string('code')->value(),
            $request->string('content')->value(),
            $request->string('transferType')->value(),
            $request->string('description')->value(),
            $request->integer('transferAmount'),
            $request->string('referenceCode')->value(),
            $request->integer('accumulated')
        );

        Log::channel('daily')->info('Parsed webhook data', [
            'id' => $sePayWebhookData->id,
            'content' => $sePayWebhookData->content,
            'transferType' => $sePayWebhookData->transferType,
            'transferAmount' => $sePayWebhookData->transferAmount,
        ]);

        // Kiểm tra transaction đã tồn tại chưa
        if (SePayTransaction::query()->where('id', $sePayWebhookData->id)->exists()) {
            Log::channel('daily')->warning('SEPAY WEBHOOK: Duplicate transaction', ['id' => $sePayWebhookData->id]);
            return response()->json(['error' => 'Transaction already processed'], 200);
        }

        // Lưu transaction
        try {
            $model = new SePayTransaction;
            $model->id = $sePayWebhookData->id;
            $model->gateway = $sePayWebhookData->gateway;
            $model->transactionDate = $sePayWebhookData->transactionDate;
            $model->accountNumber = $sePayWebhookData->accountNumber;
            $model->subAccount = $sePayWebhookData->subAccount ?? '';
            $model->code = $sePayWebhookData->code ?? '';
            $model->content = $sePayWebhookData->content;
            $model->transferType = $sePayWebhookData->transferType;
            $model->description = $sePayWebhookData->description ?? '';
            $model->transferAmount = $sePayWebhookData->transferAmount;
            $model->referenceCode = $sePayWebhookData->referenceCode ?? '';
            $model->save();
            
            Log::channel('daily')->info('Transaction saved', ['id' => $sePayWebhookData->id]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Failed to save transaction', [
                'error' => $e->getMessage(),
                'id' => $sePayWebhookData->id,
            ]);
        }

        // Match pattern để lấy booking number
        $pattern = config('sepay.pattern', 'HAPPYISLANDTOUR');
        $regex = '/\b' . preg_quote($pattern, '/') . '([a-zA-Z0-9\-_]+)/';
        
        Log::channel('daily')->info('Pattern matching', [
            'pattern' => $pattern,
            'regex' => $regex,
            'content' => $sePayWebhookData->content,
        ]);

        preg_match($regex, $sePayWebhookData->content, $matches);

        Log::channel('daily')->info('Regex matches', ['matches' => $matches]);

        if (isset($matches[0])) {
            // Lấy phần sau pattern (booking number)
            $info = Str::of($matches[0])->replaceFirst($pattern, '')->value();
            
            Log::channel('daily')->info('Extracted booking info', [
                'full_match' => $matches[0],
                'info' => $info,
            ]);

            // Dispatch event
            event(new SePayWebhookEvent($info, $sePayWebhookData));
            
            Log::channel('daily')->info('Event dispatched successfully');
        } else {
            Log::channel('daily')->warning('No pattern match found in content');
        }

        return response()->noContent();
    }

    /**
     * Get API token from Authorization header
     */
    private function getApiToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        // Thử format "Apikey xxx"
        $position = strrpos($header, 'Apikey ');
        if ($position !== false) {
            $token = substr($header, $position + 7);
            return str_contains($token, ',') ? (strstr($token, ',', true) ?: null) : $token;
        }

        // Thử format "Bearer xxx"
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }

        return null;
    }
}
