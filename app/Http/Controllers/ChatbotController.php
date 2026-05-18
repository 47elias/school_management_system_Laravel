<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Term;
use App\Models\FeeStructure;

class ChatbotController extends Controller
{
    /**
     * Display the AI Chat interface with real-time student context.
     */
    public function index()
    {
        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        $calculatedBalance = $student->calculated_balance;

        $hasIndividualFees = FeeStructure::where('term_id', $student->term_id)
            ->where('student_id', $student->id)
            ->exists();

        if ($hasIndividualFees) {
            $totalFee = FeeStructure::where('term_id', $student->term_id)
                ->where('student_id', $student->id)
                ->sum('amount');
        } else {
            $totalFee = FeeStructure::where('term_id', $student->term_id)
                ->where('grade', $student->grade)
                ->whereNull('student_id')
                ->sum('amount');
        }

        $paid = $student->payments->where('term_id', $student->term_id)->sum('amount_paid');
        $paymentPercentage = $totalFee > 0 ? ($paid / $totalFee) * 100 : 0;

        $currentTerm = $student->term ?? Term::where('is_current', true)->first();
        $avatar = $student->gender == 'Female' ? 'adminlte/dist/img/avatar3.png' : 'adminlte/dist/img/avatar5.png';

        return view('student.ai_chat', compact(
            'student',
            'calculatedBalance',
            'paymentPercentage',
            'currentTerm',
            'avatar'
        ));
    }

    /**
     * Process the chat message with a blend of data-driven accuracy and conversational flair.
     */
    public function handle(Request $request)
    {
        $request->validate(['message' => 'required|string|max:1000']);

        /** @var \App\Models\Student $student */
        $student = Auth::guard('student')->user();

        // 1. Fetch Academic Records for AI context
        $marks = $student->marks()->with('term')->get();
        $academicContext = $marks->isEmpty()
            ? "No academic records found yet."
            : $marks->map(function($m) {
                return "Subject: {$m->subject}, Mark: {$m->final_mark}, Grade: {$m->grade}, Classification: " . ($m->classification ?? 'N/A');
            })->implode(' | ');

        // 2. Prepare the Conversational System Instruction
        $schoolName = env('SCHOOL_NAME', 'The Academy');
        $currency = env('CURRENCY_SYMBOL', '$');

        $systemPrompt = "You are the friendly and professional AI Academic Assistant for {$schoolName}.

        STUDENT DATA:
        - Name: {$student->name} {$student->surname}
        - Reg Number: {$student->student_number}
        - Current Grade: {$student->grade}
        - Financials: Balance of {$currency}" . number_format($student->calculated_balance, 2) . ", Arrears: {$currency}" . number_format($student->monthly_arrears, 2) . ".
        - Academic History: {$academicContext}

        INSTRUCTIONS:
        1. BE CONVERSATIONAL: You can engage in general chat, greetings, and provide motivational or study advice.
        2. PERSONALIZATION: Address the student as {$student->name}.
        3. DATA ACCURACY: If the student asks about their grades, fees, or account status, you MUST use the STUDENT DATA provided above to give an exact answer.
        4. LIMITATIONS: If the student asks for something you don't have access to (like specific teacher names or detailed lesson plans), kindly refer them to the administration at " . env('SCHOOL_EMAIL', 'administration') . ".
        5. TONE: Be supportive, helpful, and school-appropriate.";

        // 3. API Call to Gemini
        try {
            $apiKey = config('services.gemini.key');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\nStudent Query: " . $request->message]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.8, // Slightly higher for more 'human' variety in speech
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->failed()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $result = $response->json();
            $aiReply = $result['candidates'][0]['content']['parts'][0]['text'] ?? "I'm sorry {$student->name}, I'm having a bit of trouble responding right now.";

            return response()->json(['reply' => $aiReply]);

        } catch (\Exception $e) {
            \Log::error("Chatbot Error: " . $e->getMessage());
            return response()->json(['reply' => "I'm sorry, I'm experiencing a temporary connection issue. Please try again in a moment."], 500);
        }
    }
}
