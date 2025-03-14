<?php

namespace Workbench\App\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Support\Facades\Http;
use Workbench\App\Models\User;

class SendTestRequests extends Command
{
    use InteractsWithAuthentication;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workbench:send-test-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * An array of URLs to send requests to.
     *
     * @var array
     */
    protected $urls = [
        '/user/profile',
        '/user/settings',
        '/user/notifications',
        '/user/messages',
        '/user/friends',
        '/user/photos',
        '/user/albums',
        '/user/posts',
        '/user/comments',
        '/user/likes',
        '/user/followers',
        '/user/following',
        '/user/block',
        '/user/unblock',
        '/user/report',
        '/user/search',
        '/user/invite',
        '/user/share',
        '/user/events',
        '/user/groups',
        '/user/pages',
        '/user/marketplace',
        '/user/jobs',
        '/user/ads',
        '/user/payments',
        '/user/subscriptions',
        '/user/orders',
        '/user/cart',
        '/user/wishlist',
        '/user/reviews',
        '/user/ratings',
        '/user/support',
        '/user/help',
        '/user/faq',
        '/user/terms',
        '/user/privacy',
        '/user/security',
        '/user/notifications/settings',
        '/user/messages/settings',
        '/user/friends/requests',
        '/user/friends/suggestions',
        '/user/photos/upload',
        '/user/albums/create',
        '/user/posts/create',
        '/user/comments/add',
        '/user/likes/add',
        '/user/followers/add',
        '/user/following/add',
        '/user/block/add',
        '/user/unblock/remove',
    ];

    protected array $events = [
        'clicked_button',
        'submitted_form',
        'viewed_page',
        'added_to_cart',
        'removed_from_cart',
        'completed_purchase',
        'started_checkout',
        'cancelled_order',
        'updated_profile',
        'changed_password',
        'reset_password',
        'logged_in',
        'logged_out',
        'registered_account',
        'deleted_account',
        'uploaded_photo',
        'deleted_photo',
        'liked_post',
        'unliked_post',
        'commented_on_post',
        'shared_post',
        'followed_user',
        'unfollowed_user',
        'sent_message',
        'received_message',
        'joined_group',
        'left_group',
        'created_event',
        'updated_event',
        'deleted_event',
        'rated_product',
        'reviewed_product',
        'added_friend',
        'removed_friend',
        'blocked_user',
        'unblocked_user',
        'reported_user',
        'searched_site',
        'viewed_notification',
        'cleared_notification',
        'updated_settings',
        'changed_language',
        'changed_theme',
        'viewed_ad',
        'clicked_ad',
        'added_payment_method',
        'removed_payment_method',
        'updated_subscription',
        'cancelled_subscription',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        while (true) {

            $users = User::inRandomOrder()->limit(10)->get();

            foreach ($users as $user) {

                $randomUrls = collect($this->urls)->shuffle()->take(20);

                foreach ($randomUrls as $url) {
                    $response = Http::withHeaders([
                        'email' => $user->email,
                        'password' => 'password',
                    ])->get('http://127.0.0.1:8000'.$url);
                    sleep(5);
                }

                $randomEvents = collect($this->events)->shuffle()->take(20);

                foreach ($randomEvents as $event) {
                    $response = Http::withHeaders([
                        'email' => $user->email,
                        'password' => 'password',
                    ])->post('http://127.0.0.1:8000/users/'.$user->id.'/'.$event);
                    sleep(5);
                }

            }
        }
    }
}
