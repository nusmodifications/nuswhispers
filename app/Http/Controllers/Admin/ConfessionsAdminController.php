<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Carbon\Carbon;
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
        $status = ucfirst($request->input('status', 'Pending'));

        if ($status !== 'Pending') {
            $query = Confession::orderBy('created_at', 'desc');
        } else {
            $query = Confession::orderBy('created_at', 'asc');
        }

        if ($category = $request->input('category')) {
            $query->whereHas('categories', function ($query) use ($category) {
                $query->where('confession_categories.confession_category_id', '=', (int) $category);
            });
        }

        if ($request->input('q')) {
            $search = stripslashes($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('content', 'LIKE', "%$search%");
                $q->orWhere('confession_id', (int) $search);
            });
        }

        if (($start = $request->input('start')) && ($end = $request->input('end'))) {
            $start = Carbon::createFromFormat('U', $start)->startOfDay();
            $end = Carbon::createFromFormat('U', $end)->startOfDay();

            if ($start > $end) {
                return redirect()->back()->withMessage('Start date cannot be later than end date.')
                    ->with('alert-class', 'alert-danger');
            }

            $query->where('created_at', '>=', $start->toDateTimeString());
            $query->where('created_at', '<', $end->toDateTimeString());
        }

        if ($fingerprint = $request->input('fingerprint')) {
            $query->where('fingerprint', $fingerprint);
        }

        if ($status !== 'All') {
            $query->where('status', '=', $status);
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $confessions */
        $confessions = $query->with('moderatorComments')->paginate(10)->appends($request->input());

        $categories = Category::orderBy('confession_category', 'asc')->pluck('confession_category_id', 'confession_category');

        return view('admin.confessions.index', [
            'confessions' => $confessions,
            'categories' => collect(['All Categories' => '0'])->merge($categories),
            'hasPageToken' => $this->userHasPageToken(),
        ]);
    }

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

    public function postEdit(Request $request, Confession $confession)
    {
        if ($request->input('action') === 'post_comment') {
            $request->validate([
                'comment' => 'required',
            ]);

            $comment = new ModeratorComment([
                'content' => $request->input('comment'),
                'user_id' => $request->user()->getAuthIdentifier(),
                'created_at' => new \DateTime(),
            ]);

            $confession->moderatorComments()->save($comment);

            return redirect()->back()->withMessage('Comment successfully added.')
                    ->with('alert-class', 'alert-success');
        }

        $request->validate([
            'content' => 'required',
            'categories' => 'array',
            'status' => 'in:Featured,Pending,Approved,Rejected',
        ]);

        try {
            $data = $request->only(['content', 'status', 'images', 'schedule']);
            $data['categories'] = $request->input('categories', []);

            if (env('MANUAL_MODE', false) && request()->input('fb_post_id')) {
                $data['fb_post_id'] = $request->input('fb_post_id');
            }

            $this->service->update($confession->getKey(), $data);

            return redirect()->back()->withMessage('Confession successfully updated.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Failed updating confession: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }

    public function getApprove($id, $hours = 0)
    {
        return $this->switchOrScheduleConfession($id, 'Approved', (int) $hours);
    }

    public function getFeature($id, $hours = 0)
    {
        return $this->switchOrScheduleConfession($id, 'Featured', (int) $hours);
    }

    protected function switchOrScheduleConfession($id, $status, $hours)
    {
        $confession = Confession::findOrFail($id);

        if (! $this->userHasPageToken()) {
            return redirect()->back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            if ($hours > 0) {
                $this->service->updateStatus($confession, $status, $hours);

                return redirect()->back()->withMessage('Confession has been scheduled to be ' . strtolower($status) . ' in ' . $hours . ' hour(s).')->with('alert-class', 'alert-success');
            }
            $this->service->updateStatus($confession, $status);

            return redirect()->back()->withMessage('Confession successfully ' . strtolower($status) . ' and posted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error switching status of confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getUnfeature($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (! $this->userHasPageToken()) {
            return redirect()->back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->service->updateStatus($confession, 'Approved');

            return redirect()->back()->withMessage('Confession successfully removed from featured.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error removing confession from featured: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getReject($id)
    {
        // @TODO: Move this to a repository
        $confession = Confession::findOrFail($id);

        if (! $this->userHasPageToken()) {
            return redirect()->back()->withMessage('You have not connected your account with Facebook.')->with('alert-class', 'alert-danger');
        }

        try {
            $this->service->updateStatus($confession, 'Rejected');

            return redirect()->back()->withMessage('Confession successfully rejected.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error rejecting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete($id)
    {
        try {
            $this->service->delete($id);

            return redirect()->back()->withMessage('Confession successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error deleting confession: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    protected function userHasPageToken()
    {
        $profile = auth()->user()->profiles()->where('provider_name', 'facebook')->first();

        return $profile && $profile->page_token;
    }
}
