<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /** 読書計画一覧の表示 **/
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status');

        $readingPlans = auth()->user()
            ->readingPlans()
            ->with('book')
            ->when($currentStatus, fn ($query) => $query->where('status', $currentStatus))
            ->latest('target_date')
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /** 読書計画作成画面の表示 **/
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /** 読書計画の保存 **/
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['status'] = ReadingPlanStatus::InProgress;

        ReadingPlan::create($validated);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました。');
    }

    /** 読書計画の編集画面を表示する **/
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        $books = Book::orderBy('title')->get();

        return view('reading-plans.edit', compact('readingPlan', 'books'));
    }

    /** 読書計画を更新する */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update($request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    /** 読書計画を削除する **/
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    /** 読書計画の詳細を表示する **/
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('complete', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了しました。');
    }

    /** 読書計画を再開する **/
    public function reopen(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('reopen', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を再開しました。');
    }
}
