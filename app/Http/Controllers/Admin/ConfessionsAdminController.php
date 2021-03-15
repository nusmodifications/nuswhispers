<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use NUSWhispers\Listeners\ResolvesFacebookPageToken;
use NUSWhispers\Models\Category;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Services\ConfessionService;

class ConfessionsAdminController extends AdminController
{
    use ResolvesFacebookPageToken;

    /**
     * @var \NUSWhispers\Services\ConfessionService
     */
    protected $service;

    /**
     * Constructs an instance of the controller.
     *
     * @param \NUSWhispers\Services\ConfessionService $service
     */
    public function __construct(ConfessionService $service)
    {
        $this->service = $service;
    }

    /**
     * Retrieves a list of confessions.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function getIndex(Request $request)
    {
        $query = $this->buildQueryFromRequest($request);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $confessions */
        $confessions = $query->with('moderatorComments')
            ->paginate(10)
            ->appends($request->input());

        $categories = Category::query()
            ->orderBy('confession_category', 'asc')
            ->pluck('confession_category_id', 'confession_category');

        return view('admin.confessions.index', [
            'confessions' => $confessions,
            'categories' => collect(['All Categories' => '0'])->merge($categories),
            'hasPageToken' => $this->userHasPageToken($request),
        ]);
    }

    /**
     * Displays the form to edit an existing confession.
     *
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed
     */
    public function getEdit(Confession $confession)
    {
        return view('admin.confessions.edit', [
            'categories' => Category::categoryAsc()->get(),
            'confession' => $confession->load([
                'moderatorComments',
                'moderatorComments.user',
                'logs',
                'logs.user',
            ]),
        ]);
    }

    /**
     * Updates an existing confession.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postEdit(Request $request, Confession $confession)
    {
        if ($request->input('action') === 'post_comment') {
            $this->validate($request, [
                'comment' => 'required',
            ]);

            return $this->withErrorHandling(function () use ($confession, $request) {
                $confession->moderatorComments()->save(new ModeratorComment([
                    'content' => $request->input('comment'),
                    'user_id' => $request->user()->getAuthIdentifier(),
                    'created_at' => new \DateTime(),
                ]));

                return $this->backWithSuccess('Comment successfully added.');
            });
        }

        $this->validate($request, [
            'content' => 'required',
            'categories' => 'array',
            'status' => 'in:Featured,Pending,Approved,Rejected',
        ]);

        return $this->withErrorHandling(function () use ($confession, $request) {
            $data = $request->only(['content', 'status', 'images', 'schedule']);
            $data['categories'] = $request->input('categories', []);

            if (env('MANUAL_MODE', false) && request()->input('fb_post_id')) {
                $data['fb_post_id'] = $request->input('fb_post_id');
            }

            $this->service->update($confession->getKey(), $data);

            return $this->backWithSuccess('Confession successfully updated.');
        });
    }

    /**
     * Approves a confession.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     * @param mixed $hours
     *
     * @return mixed
     */
    public function getApprove(Request $request, Confession $confession, $hours = 0)
    {
        return $this->switchOrScheduleConfession($request, $confession, 'Approved', (int) $hours);
    }

    /**
     * Features a confession.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     * @param mixed $hours
     *
     * @return mixed
     */
    public function getFeature(Request $request, Confession $confession, $hours = 0)
    {
        return $this->switchOrScheduleConfession($request, $confession, 'Featured', (int) $hours);
    }

    /**
     * Switch or schedule a confession.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     * @param string $status
     * @param int $hours
     *
     * @return mixed
     */
    protected function switchOrScheduleConfession(Request $request, Confession $confession, string $status, int $hours)
    {
        // Commenting out for now until we FB allows us to post to the page again.
        // if (!$this->userHasPageToken($request)) {
        //     return $this->backWithError('You have not connected your account with Facebook.');
        // }

        return $this->withErrorHandling(function () use ($confession, $status, $hours) {
            if ($hours > 0) {
                $this->service->updateStatus($confession, $status, $hours);

                return $this->backWithSuccess('Confession has been scheduled to be ' . strtolower($status) . ' in ' . $hours . ' hour(s).');
            }

            $this->service->updateStatus($confession, $status);

            return $this->backWithSuccess('Confession successfully ' . strtolower($status) . ' and posted.');
        });
    }

    /**
     * Removes a confession from Featured.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed
     */
    public function getUnfeature(Request $request, Confession $confession)
    {
        // Commenting out for now until we FB allows us to post to the page again.
        // if (!$this->userHasPageToken($request)) {
        //     return $this->backWithError('You have not connected your account with Facebook.');
        // }

        return $this->withErrorHandling(function () use ($confession) {
            $this->service->updateStatus($confession, 'Approved');

            return $this->backWithSuccess('Confession successfully removed from featured.');
        });
    }

    /**
     * Rejects a confession.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed
     */
    public function getReject(Request $request, Confession $confession)
    {
        // Commenting out for now until we FB allows us to post to the page again.
        // if (!$this->userHasPageToken($request)) {
        //     return $this->backWithError('You have not connected your account with Facebook.');
        // }

        return $this->withErrorHandling(function () use ($confession) {
            $this->service->updateStatus($confession, 'Rejected');

            return $this->backWithSuccess('Confession successfully rejected.');
        });
    }

    /**
     * Deletes a confession.
     *
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed
     */
    public function getDelete(Confession $confession)
    {
        return $this->withErrorHandling(function () use ($confession) {
            $this->service->delete($confession);

            return $this->backWithSuccess('Confession successfully deleted.');
        });
    }

    /**
     * Builds query from request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildQueryFromRequest(Request $request): Builder
    {
        $status = ucfirst($request->input('status', 'Pending'));

        $query = Confession::query()->orderBy('created_at', $status !== 'Pending' ? 'desc' : 'asc');

        if ($category = $request->input('category')) {
            $query->whereHas('categories', function (Builder $query) use ($category) {
                $query->where('confession_categories.confession_category_id', '=', (int) $category);
            });
        }

        if ($request->input('q')) {
            $search = stripslashes($request->input('q'));
            $query->where(function (Builder $q) use ($search) {
                return $q->where('content', 'LIKE', "%$search%")
                    ->orWhere('confession_id', (int) $search);
            });
        }

        if (($start = $request->input('start')) && ($end = $request->input('end'))) {
            $start = Carbon::createFromFormat('U', $start)->startOfDay();
            $end = Carbon::createFromFormat('U', $end)->startOfDay();

            $query->where('created_at', '>=', $start->toDateTimeString());
            $query->where('created_at', '<', $end->toDateTimeString());
        }

        if ($fingerprint = $request->input('fingerprint')) {
            $query->where('fingerprint', $fingerprint);
        }

        if ($status !== 'All') {
            $query->where('status', '=', $status);
        }

        return $query;
    }

    /**
     * Checks if the user has page token.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function userHasPageToken(Request $request): bool
    {
        $profile = $request->user()->profiles()->where('provider_name', 'facebook')->first();

        return $profile && $profile->page_token;
    }
}
