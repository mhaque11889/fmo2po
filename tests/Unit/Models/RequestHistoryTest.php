<?php

namespace Tests\Unit\Models;

use App\Models\RequestHistory;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RequestHistoryTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_history_has_fillable_attributes(): void
    {
        $request = $this->createPendingRequest();
        $user = $request->creator;

        $history = RequestHistory::factory()
            ->forRequest($request)
            ->byUser($user)
            ->create([
                'action' => 'created',
                'changes' => ['field' => 'value'],
                'remarks' => 'Test remarks',
            ]);

        $this->assertEquals('created', $history->action);
        $this->assertEquals(['field' => 'value'], $history->changes);
        $this->assertEquals('Test remarks', $history->remarks);
    }

    public function test_history_belongs_to_request(): void
    {
        $request = $this->createPendingRequest();
        $history = $this->createHistoryEntry($request, $request->creator);

        $this->assertInstanceOf(RequirementRequest::class, $history->request);
        $this->assertEquals($request->id, $history->request->id);
    }

    public function test_history_belongs_to_user(): void
    {
        $user = $this->createFmoUser();
        $request = $this->createPendingRequest($user);
        $history = $this->createHistoryEntry($request, $user);

        $this->assertInstanceOf(User::class, $history->user);
        $this->assertEquals($user->id, $history->user->id);
    }

    public function test_changes_are_cast_to_array(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->edited(['item' => ['old' => 'Old', 'new' => 'New']])
            ->create();

        $this->assertIsArray($history->changes);
        $this->assertArrayHasKey('item', $history->changes);
    }

    public function test_action_description_accessor_for_created(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->created()
            ->create();

        $this->assertEquals('Request created', $history->action_description);
    }

    public function test_action_description_accessor_for_edited(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->edited()
            ->create();

        $this->assertEquals('Request details modified', $history->action_description);
    }

    public function test_action_description_accessor_for_approved(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->approved()
            ->create();

        $this->assertEquals('Request approved', $history->action_description);
    }

    public function test_action_description_accessor_for_rejected(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->rejected()
            ->create();

        $this->assertEquals('Request rejected', $history->action_description);
    }

    public function test_action_description_accessor_for_cancelled(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->cancelled()
            ->create();

        $this->assertEquals('Request cancelled', $history->action_description);
    }

    public function test_action_description_accessor_for_assigned(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->assigned()
            ->create();

        $this->assertEquals('Request assigned', $history->action_description);
    }

    public function test_action_description_accessor_for_in_progress(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->inProgress()
            ->create();

        $this->assertEquals('Marked as in progress', $history->action_description);
    }

    public function test_action_description_accessor_for_completed(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->completed()
            ->create();

        $this->assertEquals('Marked as completed', $history->action_description);
    }

    public function test_action_description_accessor_for_unknown_action(): void
    {
        $request = $this->createPendingRequest();
        $history = RequestHistory::factory()
            ->forRequest($request)
            ->create(['action' => 'custom_action']);

        $this->assertEquals('Custom_action', $history->action_description);
    }

    public function test_history_uses_correct_table_name(): void
    {
        $history = new RequestHistory();

        $this->assertEquals('request_history', $history->getTable());
    }
}
