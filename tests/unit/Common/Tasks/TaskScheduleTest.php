<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Common\Tasks;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Fisharebest\Webtrees\TestCase;
use MyArtJaub\Webtrees\Common\Tasks\TaskSchedule;

/**
 * Class TaskScheduleTest.
 *
 * @covers \MyArtJaub\Webtrees\Common\Tasks\TaskSchedule
 */
class TaskScheduleTest extends TestCase
{
    protected TaskSchedule $task_schedule;
    protected int $id;
    protected string $task_id;
    protected int $last_run;
    protected CarbonInterface $last_run_date;
    protected int $frequency;
    protected int $remaining;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->id = 42;
        $this->task_id = 'task_test';
        $this->last_run = 252075600;
        $this->last_run_date = CarbonImmutable::createFromTimestamp($this->last_run, 'UTC');
        $this->frequency = 17;
        $this->remaining = 9;

        $this->task_schedule = new TaskSchedule(
            $this->id,
            $this->task_id,
            true,
            $this->last_run_date,
            true,
            $this->frequency,
            $this->remaining,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->task_schedule);
        unset($this->id);
        unset($this->task_id);
        unset($this->last_run_date);
        unset($this->frequency);
        unset($this->remaining);
    }

    public function testProperties(): void
    {
        self::assertSame($this->id, $this->task_schedule->id());
        self::assertSame($this->task_id, $this->task_schedule->taskId());
        self::assertTrue($this->task_schedule->isEnabled());
        self::assertSame($this->last_run, $this->task_schedule->lastRunTime()->getTimestamp());
        self::assertTrue($this->task_schedule->wasLastRunSuccess());
        self::assertSame($this->frequency, $this->task_schedule->frequency());
        self::assertSame($this->remaining, $this->task_schedule->remainingOccurrences());
        self::assertTrue($this->task_schedule->isRunning());
    }


    public function testSetFrequency(): void
    {
        $this->task_schedule->setFrequency(100);
        self::assertSame(100, $this->task_schedule->frequency());
    }

    public function testSetLastResult(): void
    {
        $this->task_schedule->setLastResult(false);
        self::assertFalse($this->task_schedule->wasLastRunSuccess());
    }

    public function testSetLastRunTime(): void
    {
        $this->task_schedule->setLastRunTime(CarbonImmutable::createFromTimestamp(1390568400, 'UTC'));
        self::assertSame(1390568400, $this->task_schedule->lastRunTime()->getTimestamp());
    }

    public function testSetRemainingOccurrences(): void
    {
        $this->task_schedule->setRemainingOccurrences(30);
        self::assertSame(30, $this->task_schedule->remainingOccurrences());
    }

    public function testSetStatus(): void
    {
        $this->task_schedule->disable();
        self::assertFalse($this->task_schedule->isEnabled());

        $this->task_schedule->enable();
        self::assertTrue($this->task_schedule->isEnabled());
    }

    public function testRunTask(): void
    {
        $this->task_schedule->stopRunning();
        self::assertFalse($this->task_schedule->isRunning());

        $this->task_schedule->startRunning();
        self::assertTrue($this->task_schedule->isRunning());
    }

    public function testToArray(): void
    {
        self::assertEqualsCanonicalizing([
            'id'            =>  $this->id,
            'task_id'       =>  $this->task_id,
            'enabled'       =>  true,
            'last_run'      =>  $this->last_run_date,
            'last_result'   =>  true,
            'frequency'     =>  $this->frequency,
            'nb_occurrences' =>  $this->remaining,
            'is_running'    =>  true
        ], $this->task_schedule->toArray());
    }

    public function testDecrementRemainingOccurrences(): void
    {
        $this->task_schedule->decrementRemainingOccurrences();
        self::assertSame($this->remaining - 1, $this->task_schedule->remainingOccurrences());

        $this->task_schedule->enable();
        $this->task_schedule->setRemainingOccurrences(1);
        $this->task_schedule->decrementRemainingOccurrences();
        self::assertSame(0, $this->task_schedule->remainingOccurrences());
        self::assertFalse($this->task_schedule->isEnabled());
    }
}
