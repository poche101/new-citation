<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupCitation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupCitationController extends Controller
{
    /**
     * Show the multi-step group citation form
     */
    public function create($groupId = null)
    {
        $groups = Group::all();
        $selectedGroup = $groupId ? Group::find($groupId) : null;

        return view('components.group-form', compact('groups', 'selectedGroup'));
    }

    /**
     * Store the submitted citation
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'phone'       => 'required|string|max:20',
            'kingschat'   => 'required|string|max:255',
            'unit'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'title'       => 'nullable|string|max:255',
            'period'      => 'nullable|string|max:255',
            'group_id'    => ['nullable', 'exists:groups,id'], // optional
            'description' => ['required', 'string', function ($attribute, $value, $fail) {
                if (str_word_count($value) > 150) {
                    $fail("The {$attribute} may not be greater than 150 words.");
                }
            }],
        ]);

        $periodFrom = $periodTo = null;

        if ($request->filled('period')) {
            // Detect separator
            $separator = false;
            if (strpos($request->period, '–') !== false) {
                $separator = '–'; // en dash
            } elseif (strpos($request->period, ' to ') !== false) {
                $separator = ' to '; // " to "
            }

            if ($separator) {
                $dates = explode($separator, $request->period);
                try {
                    $periodFrom = isset($dates[0]) ? Carbon::parse(trim($dates[0])) : null;
                    $periodTo   = isset($dates[1]) ? Carbon::parse(trim($dates[1])) : null;
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'period' => 'Invalid period format. Use "YYYY-MM-DD – YYYY-MM-DD" or "June 17, 2025 to December 18, 2025".'
                    ])->withInput();
                }
            } else {
                return back()->withErrors([
                    'period' => 'Invalid period format. Use "YYYY-MM-DD – YYYY-MM-DD" or "June 17, 2025 to December 18, 2025".'
                ])->withInput();
            }
        }

        GroupCitation::create([
            'name'        => $request->name,
            'phone'       => $request->phone,
            'kingschat'   => $request->kingschat,
            'unit'        => $request->unit,
            'designation' => $request->designation,
            'title'       => $request->title,
           'group_id' => $request->group_id,
            'period_from' => $periodFrom,
            'period_to'   => $periodTo,
            'description' => $request->description,
        ]);

        return redirect()->route('group-citations.create')
                         ->with('success', 'Group Citation submitted successfully!');
    }
}
