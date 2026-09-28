<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the In-App Practice Notifications Feed (Sec 13, p. 15).
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'category' => ['nullable', 'in:all,appointments,patients,clinical,billing'],
            'status' => ['nullable', 'in:all,unread,read'],
        ]);
        $filter = $validated['category'] ?? 'all';
        $status = $validated['status'] ?? 'all';
        $query = $request->user()->notifications()->latest();

        if ($filter !== 'all') {
            $query->where('data->category', $filter);
        }

        if ($status === 'unread') {
            $query->whereNull('read_at');
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'currentFilter' => $filter,
            'currentStatus' => $status,
            'unreadNotificationCount' => $request->user()->unreadNotifications()->count(),
            'readNotificationCount' => $request->user()->readNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markUnread(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsUnread();

        return back()->with('success', 'Notification marked as unread.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $record */
        $record = $request->user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        $destination = $record->data['url'] ?? route('notifications.index');
        $path = parse_url($destination, PHP_URL_PATH);
        $query = parse_url($destination, PHP_URL_QUERY);

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return redirect()->route('notifications.index');
        }

        return redirect()->to($path.($query ? '?'.$query : ''));
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $request->user()->readNotifications()->delete();

        return back()->with('success', 'Read notifications cleared.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
