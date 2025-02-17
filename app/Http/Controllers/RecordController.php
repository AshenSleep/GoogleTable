<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;

class RecordController extends Controller
{
    public function index()
    {
        $records = Record::all();
        return view('records.index', compact('records'));
    }

    public function create()
    {
        return view('records.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:255',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        Record::create($request->all());

        return redirect()->route('records.index');
    }

    public function edit(Record $record)
    {
        return view('records.edit', compact('record'));
    }

    public function update(Request $request, Record $record)
    {
        $request->validate([
            'content' => 'required|string|max:255',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        $record->update($request->all());

        return redirect()->route('records.index');
    }

    public function destroy(Record $record)
    {
        $record->delete();

        return redirect()->route('records.index');
    }

    public function generate()
    {
        Record::factory()->count(1000)->create();
        return redirect()->route('records.index');
    }

    public function clear()
    {
        Record::truncate();
        return redirect()->route('records.index');
    }
}
