<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCitation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupCitationController extends Controller
{
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
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'name'        => 'required|string|max:255',
            'unit'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'kingschat'   => 'nullable|string|max:255',
            'phone'       => 'required|string|max:20',
            'group_name'  => 'required|string|max:255',
            'period'      => 'nullable|string',
            'description' => [
                'required',
                'string',
                function ($attr, $value, $fail) {
                    if (str_word_count($value) > 150) {
                        $fail('Citation must not exceed 150 words.');
                    }
                },
            ],
        ]);

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

        GroupCitation::create([
            'title'       => $request->title,
            'name'        => $request->name,
            'unit'        => $request->unit,
            'designation' => $request->designation,
            'kingschat'   => $request->kingschat,
            'phone'       => $request->phone,
            'group_name'  => $request->group_name,
            'period_from' => $periodFrom,
            'period_to'   => $periodTo,
            'description' => $request->description,
        ]);

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


