<?php

namespace App\Http\Controllers;

use App\Models\ChatbotFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotFaqController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = ChatbotFaq::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pertanyaan', 'like', "%{$search}%")
                  ->orWhere('jawaban', 'like', "%{$search}%")
                  ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        $faqs = $query->latest()->paginate($perPage)->withQueryString();

        return view('pages.master.chatbot_faq.index', compact('faqs'));
    }

    public function create()
    {
        return view('pages.master.chatbot_faq.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateFaq($request);

        ChatbotFaq::create(array_merge($data, [
            'created_by' => Auth::guard('pengguna')->id(),
        ]));

        return redirect()->route('chatbot_faq.index')->with('success', 'FAQ Chatbot "' . $request->pertanyaan . '" berhasil dibuat.');
    }

    public function edit($id)
    {
        $faq = ChatbotFaq::findOrFail($id);

        return view('pages.master.chatbot_faq.edit', compact('faq'));
    }

    public function update(Request $request, $id)
    {
        $faq = ChatbotFaq::findOrFail($id);
        $data = $this->validateFaq($request);

        $faq->update($data);

        return redirect()->route('chatbot_faq.index')->with('success', 'FAQ Chatbot "' . $faq->pertanyaan . '" berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $faq = ChatbotFaq::findOrFail($id);
        $pertanyaan = $faq->pertanyaan;
        $faq->delete();

        return redirect()->route('chatbot_faq.index')->with('success', 'FAQ Chatbot "' . $pertanyaan . '" berhasil dihapus.');
    }

    /**
     * Tandai FAQ sebagai tersinkronisasi ke website.
     */
    public function sync($id)
    {
        $faq = ChatbotFaq::findOrFail($id);
        $faq->update([
            'is_sync'   => true,
            'synced_at' => now(),
        ]);

        return redirect()->route('chatbot_faq.index')->with('success', 'FAQ Chatbot "' . $faq->pertanyaan . '" berhasil disinkronkan ke website.');
    }

    private function validateFaq(Request $request): array
    {
        $validated = $request->validate([
            'pertanyaan'          => 'required|string|max:255',
            'jawaban'             => 'required|string',
            'quick_question'      => 'nullable',
            'quick_question_order'=> 'nullable|integer|min:0',
            'keywords'            => 'nullable|string|max:1000',
            'urutan'              => 'nullable|integer|min:0',
            'is_active'           => 'nullable',
        ]);

        return [
            'pertanyaan'           => $request->pertanyaan,
            'jawaban'              => $request->jawaban,
            'quick_question'       => $request->boolean('quick_question'),
            'quick_question_order' => $request->filled('quick_question_order')
                ? (int) $request->quick_question_order
                : null,
            'keywords'             => $this->parseKeywords($request->keywords),
            'urutan'               => $request->filled('urutan') ? (int) $request->urutan : null,
            'is_active'            => $request->boolean('is_active'),
        ];
    }

    private function parseKeywords(?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $keywords = array_filter(array_map('trim', explode(',', $value)));

        return $keywords ? array_values($keywords) : null;
    }
}