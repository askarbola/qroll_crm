<?php

namespace Webkul\Admin\Http\Controllers\Configuration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\ConfigurationForm;
use Webkul\Core\Repositories\CoreConfigRepository as ConfigurationRepository;

class ConfigurationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected ConfigurationRepository $configurationRepository) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        if (
            request()->route('slug')
            && request()->route('slug2')
        ) {
            return view('admin::configuration.edit');
        }

        return view('admin::configuration.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ConfigurationForm $request): RedirectResponse
    {
        Event::dispatch('core.configuration.save.before');

        $this->configurationRepository->create($request->all());

        Event::dispatch('core.configuration.save.after');

        session()->flash('success', trans('admin::app.configuration.index.save-success'));

        return redirect()->back();
    }

    /**
     * download the file for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download()
    {
        $path = request()->route()->parameters()['path'];

        $fileName = 'configuration/'.$path;

        $config = $this->configurationRepository->findOneByField('value', $fileName);

        return Storage::download($config['value']);
    }

    /**
     * Search for configurations.
     */
    public function search(): JsonResponse
    {
        $results = $this->configurationRepository->search(
            system_config()->getItems(),
            request()->query('query')
        );

        return new JsonResponse([
            'data' => $results,
        ]);
    }

    /**
     * Test Telegram bot connection by validating token and sending a test message.
     */
    public function testTelegramConnection(Request $request): JsonResponse
    {
        $request->validate([
            'bot_token' => 'required|string',
            'chat_id'   => 'required|integer',
        ]);

        $botToken = $request->input('bot_token');
        $chatId = (int) $request->input('chat_id');

        $client = new Client([
            'timeout'         => 10,
            'connect_timeout' => 5,
            'http_errors'     => false,
        ]);

        // Step 1: Test bot token with getMe
        try {
            $response = $client->request('GET', "https://api.telegram.org/bot{$botToken}/getMe");
            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || ! $body['ok']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $body['description'] ?? 'Invalid bot token.',
                ], 400);
            }

            $botName = $body['result']['first_name'];
        } catch (RequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ], 500);
        }

        // Step 2: Test sending message to group
        try {
            $response = $client->request('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id'    => $chatId,
                    'text'       => "Test message from Krayin CRM\n\nTelegram notifications configured successfully!",
                    'parse_mode' => 'HTML',
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== 200 || ! $body['ok']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $body['description'] ?? 'Failed to send message to group.',
                ], 400);
            }

            return new JsonResponse([
                'success' => true,
                'message' => "Connection successful! Test message sent via bot '{$botName}'.",
            ]);
        } catch (RequestException $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to send message: '.$e->getMessage(),
            ], 500);
        }
    }
}
