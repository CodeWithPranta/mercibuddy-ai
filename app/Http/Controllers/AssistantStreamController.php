<?php

namespace App\Http\Controllers;

use App\Ai\ChatResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantStreamController extends Controller
{
    public function send(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|min:2|max:500',
        ]);

        $question = trim($validated['question']);
        $history = collect(session('assistant_chat', []))
            ->map(fn (array $message): array => [
                'role' => $message['role'],
                'text' => $message['text'],
            ])
            ->slice(-12)
            ->values()
            ->all();

        // Generate the reply BEFORE streaming. Session writes made inside
        // a streamed-response callback happen after Laravel has already
        // saved the session, so the assistant reply was silently lost and
        // the chat looked empty after a page refresh.
        $result = (new ChatResponder($history))->respond($question);

        // Persist both messages right away so they survive refreshes and
        // dropped connections.
        $messages = array_slice([...session('assistant_chat', []), ['role' => 'user', 'text' => $question]], -30);
        $messages = array_slice([...$messages, [
            'role' => 'assistant',
            'text' => $result['message'],
            'artisans' => $result['artisans'],
        ]], -30);
        session()->put('assistant_chat', $messages);

        set_time_limit(0);

        return response()->stream(function () use ($result): void {
            $this->streamText($result['message']);

            $this->sendEvent('done', [
                'message' => $result['message'],
                'artisans' => $result['artisans'],
            ]);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function clear(): JsonResponse
    {
        session()->forget('assistant_chat');

        return response()->json(['cleared' => true]);
    }

    /**
     * Play the reply back in small word chunks so it feels generated
     * live instead of arriving as one delayed block.
     */
    private function streamText(string $message): void
    {
        $words = preg_split('/(\s+)/u', $message, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        $buffer = '';

        foreach ($words as $word) {
            $buffer .= $word;

            if (mb_substr($word, -1) === ' ' && mb_strlen($buffer) >= 24) {
                $this->sendEvent('token', ['text' => $buffer]);
                $buffer = '';
                usleep(35000);
            }
        }

        if ($buffer !== '') {
            $this->sendEvent('token', ['text' => $buffer]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendEvent(string $event, array $data): void
    {
        echo 'event: '.$event."\n";
        echo 'data: '.json_encode($data)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
