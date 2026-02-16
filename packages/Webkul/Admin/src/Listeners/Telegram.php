<?php

namespace Webkul\Admin\Listeners;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Telegram
{
    /**
     * Handle lead stage change notifications.
     *
     * Sends a formatted Telegram message when a lead's pipeline stage changes.
     * Silently skips if Telegram is not configured or if the update was not a stage change.
     *
     * @param  \Webkul\Lead\Models\Lead  $lead
     * @return void
     */
    public function notifyStageChange($lead)
    {
        try {
            // Skip if the stage did not actually change
            if (! $lead->wasChanged('lead_pipeline_stage_id')) {
                return;
            }

            // Retrieve Telegram configuration
            $botToken = system_config()->getConfigData('telegram.settings.connection.bot_token');
            $chatId = system_config()->getConfigData('telegram.settings.connection.chat_id');

            // Skip silently if Telegram is not configured
            if (empty($botToken) || empty($chatId)) {
                return;
            }

            // Cast chat_id to integer (Telegram group IDs are negative integers)
            $chatId = (int) $chatId;

            // Load relationships needed for the message
            $lead->loadMissing(['user', 'stage', 'pipeline']);

            // Build message components
            $repName = $lead->user?->name ?? 'Unknown';
            $leadTitle = $lead->title ?? 'Untitled Lead';
            $stageName = $lead->stage?->name ?? 'Unknown';
            $pipelineName = $lead->pipeline?->name ?? 'Unknown';
            $leadUrl = route('admin.leads.view', ['id' => $lead->id]);

            // Build HTML-formatted Telegram message
            $message = "<b>Lead Stage Updated</b>\n\n"
                ."Rep: <b>{$this->escapeHtml($repName)}</b>\n"
                ."Lead: <b>{$this->escapeHtml($leadTitle)}</b>\n"
                ."Pipeline: <b>{$this->escapeHtml($pipelineName)}</b>\n"
                ."New Stage: <b>{$this->escapeHtml($stageName)}</b>\n\n"
                ."<a href=\"{$leadUrl}\">View in CRM</a>";

            // Send message via Telegram Bot API
            $client = new Client([
                'timeout'         => 10,
                'connect_timeout' => 5,
                'http_errors'     => false,
            ]);

            $response = $client->request('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id'    => $chatId,
                    'text'       => $message,
                    'parse_mode' => 'HTML',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || empty($body['ok'])) {
                Log::warning('Telegram stage notification failed', [
                    'lead_id'     => $lead->id,
                    'status_code' => $response->getStatusCode(),
                    'response'    => $body['description'] ?? 'Unknown error',
                ]);
            }
        } catch (\Throwable $e) {
            // Never throw — stage changes must complete regardless of Telegram status
            Log::error('Telegram stage notification error', [
                'lead_id' => $lead->id ?? null,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Escape special HTML characters for Telegram's HTML parse mode.
     *
     * Telegram HTML supports only: <b>, <i>, <u>, <s>, <a>, <code>, <pre>
     * All other HTML entities must be escaped.
     */
    protected function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
