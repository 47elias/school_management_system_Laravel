<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::latest()->get();
        return view('newsletters.index', compact('newsletters'));
    }

    public function create()
    {
        return view('newsletters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|string',
        ]);

        Newsletter::create([
            'title' => $request->title,
            'subject' => $request->subject,
            'content' => $request->content,
            'target_audience' => $request->target_audience,
            'sent_at' => now(),
        ]);

        return redirect()->route('newsletters.index')->with('success', 'Newsletter created and broadcasted successfully!');
    }

    public function destroy($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        return redirect()->route('newsletters.index')->with('success', 'Newsletter deleted successfully!');
    }
}