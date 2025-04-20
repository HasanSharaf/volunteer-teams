<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Http\Resources\ChatResource;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['sender', 'receiver'])->paginate(10);
        return ChatResource::collection($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'sender_type' => 'required|in:volunteer,team',
            'receiver_type' => 'required|in:volunteer,team',
        ]);

        $chat = Chat::create($request->all());
        return new ChatResource($chat);
    }

    public function show(Chat $chat)
    {
        return new ChatResource($chat->load(['sender', 'receiver']));
    }

    public function update(Request $request, Chat $chat)
    {
        $request->validate([
            'message' => 'sometimes|string',
            'sender_id' => 'sometimes|exists:users,id',
            'receiver_id' => 'sometimes|exists:users,id',
            'sender_type' => 'sometimes|in:volunteer,team',
            'receiver_type' => 'sometimes|in:volunteer,team',
        ]);

        $chat->update($request->all());
        return new ChatResource($chat);
    }

    public function destroy(Chat $chat)
    {
        $chat->delete();
        return response()->json(['message' => 'Chat message deleted successfully']);
    }

    public function getChatHistory($senderId, $receiverId)
    {
        $chats = Chat::where(function($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return ChatResource::collection($chats);
    }
} 