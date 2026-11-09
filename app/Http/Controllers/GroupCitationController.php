<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCitationSubmission;
use App\Models\Group;
use App\Models\GroupCitation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupCitationController extends Controller
{
    use HandlesCitationSubmission;

    /**
     * Show the multi-step form
     */
    public function create()
    {
        $groups = Group::all();
        return view('components.group-form', compact('groups'));
    }

    /**
     * Store the citation
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'title'       => 'nullable|string|max:255',
                'name'        => 'required|string|max:255',
                'unit'        => 'required|string|max:255',
                'designation' => 'required|string|max:255',
                'kingschat'   => 'nullable|string|max:255',
                'phone'       => 'required|string|max:20',
                'group_name'  => 'required|string|max:255',
                'period'      => 'nullable|string',
                // Personal citation (max 150 words)
                'description' => $this->citationRules(),
            ] + $this->submissionRules(),
            $this->submissionMessages(),
            $this->submissionAttributes() + ['description' => 'personal citation']
        );

        $periodFrom = null;
        $periodTo   = null;

        if ($request->filled('period')) {
            try {
                [$from, $to] = preg_split('/–| to /', $request->period);
                $periodFrom = Carbon::parse(trim($from));
                $periodTo   = Carbon::parse(trim($to));
            } catch (\Exception $e) {
                return back()->withErrors(['period' => 'Invalid period format.'])->withInput();
            }
        }

        $this->createWithAttachments(GroupCitation::class, [
            'title'       => $data['title'] ?? null,
            'name'        => $data['name'],
            'unit'        => $data['unit'],
            'designation' => $data['designation'],
            'kingschat'   => $data['kingschat'] ?? null,
            'phone'       => $data['phone'],
            'group_name'  => $data['group_name'],
            'period_from' => $periodFrom,
            'period_to'   => $periodTo,
            'description' => $data['description'],

            'unit_citation'           => $data['unit_citation'],
            'personal_report_summary' => $data['personal_report_summary'],
            'personal_report_detail'  => $data['personal_report_detail'],
            'unit_report_summary'     => $data['unit_report_summary'],
            'unit_report_detail'      => $data['unit_report_detail'],
        ], $request);

        return redirect()->route('group-citations.create')->with('success', 'Citation submitted successfully.');
    }

    /**
     * Toggle approval status
     */
    public function toggleApproval($id)
    {
        $citation = GroupCitation::findOrFail($id);
        $citation->approved = !$citation->approved;
        $citation->save();

        return redirect()->back()->with('success', 'Approval status updated.');
    }

    /**
     * Store, update, or delete admin comment
     */

    public function storeComment(Request $request, $id)
{
    $citation = GroupCitation::findOrFail($id);

    $data = json_decode($request->getContent(), true);

    $comment = $data['comment'] ?? null;

    $citation->admin_comment = $comment;
    $citation->save();

    return response()->json([
        'success' => true,
        'comment' => $citation->admin_comment
    ]);
}

}


