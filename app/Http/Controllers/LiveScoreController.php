<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\LiveDelivery;
use App\Models\LiveInnings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LiveScoreController extends Controller
{
    public function index(): View
    {
        $fixtures = Fixture::with(['homeTeam', 'awayTeam'])
            ->latest()
            ->paginate(15);

        return view('admin.live.index', compact('fixtures'));
    }

    public function saveToss(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'toss_winner_team_id' => [
                'required',
                'integer',
                Rule::in([
                    $fixture->home_team_id,
                    $fixture->away_team_id,
                ]),
            ],
            'toss_decision' => ['required', Rule::in(['bat', 'field'])],
        ]);

        $fixture->update($data);

        return back()->with('status', 'Toss details saved.');
    }

    public function show(Fixture $fixture): View
    {
        $fixture->load(['homeTeam', 'awayTeam', 'tossWinner']);

        $innings = LiveInnings::with([
            'battingTeam',
            'bowlingTeam',
            'deliveries',
        ])
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        $current = $innings->firstWhere('is_current', true)
            ?? $innings->last();

        $batters = collect();
        $bowlers = collect();

        if ($current) {
            foreach ($current->deliveries as $delivery) {
                $name = $delivery->striker;

                if (!$batters->has($name)) {
                    $batters->put($name, [
                        'name' => $name,
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'out' => false,
                        'dismissal' => null,
                    ]);
                }

                $batter = $batters->get($name);
                $batter['runs'] += (int) $delivery->batter_runs;
                $batter['fours'] += (int) ($delivery->batter_runs === 4);
                $batter['sixes'] += (int) ($delivery->batter_runs === 6);

                if ($delivery->extra_type !== 'wide') {
                    $batter['balls']++;
                }

                $batters->put($name, $batter);

                if ($delivery->is_wicket && $delivery->dismissed_batter) {
                    $dismissedName = $delivery->dismissed_batter;

                    if (!$batters->has($dismissedName)) {
                        $batters->put($dismissedName, [
                            'name' => $dismissedName,
                            'runs' => 0,
                            'balls' => 0,
                            'fours' => 0,
                            'sixes' => 0,
                            'out' => false,
                            'dismissal' => null,
                        ]);
                    }

                    $dismissed = $batters->get($dismissedName);
                    $dismissed['out'] = true;
                    $dismissed['dismissal'] = $delivery->wicket_type;
                    $batters->put($dismissedName, $dismissed);
                }

                $bowlerName = $delivery->bowler;

                if (!$bowlers->has($bowlerName)) {
                    $bowlers->put($bowlerName, [
                        'name' => $bowlerName,
                        'runs' => 0,
                        'wickets' => 0,
                        'legal_balls' => 0,
                    ]);
                }

                $bowler = $bowlers->get($bowlerName);

                // Bat runs and wides/no-ball extras are charged to the bowler.
                $bowler['runs'] += (int) $delivery->batter_runs;

                if (in_array($delivery->extra_type, ['wide', 'no_ball'], true)) {
                    $bowler['runs'] += (int) $delivery->extra_runs;
                }

                if ($delivery->isBowlerChargedWicket()) {
                    $bowler['wickets']++;
                }

                if ($delivery->counts_as_legal_ball) {
                    $bowler['legal_balls']++;
                }

                $bowlers->put($bowlerName, $bowler);
            }
        }

        return view('live.show', compact(
            'fixture',
            'innings',
            'current',
            'batters',
            'bowlers'
        ));
    }

    public function admin(Fixture $fixture): View
    {
        $fixture->load(['homeTeam', 'awayTeam', 'tossWinner']);

        $innings = LiveInnings::with([
            'battingTeam',
            'bowlingTeam',
            'deliveries',
        ])
            ->where('fixture_id', $fixture->id)
            ->orderBy('innings_number')
            ->get();

        return view('admin.live.score', compact('fixture', 'innings'));
    }

    public function startInnings(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'innings_number' => ['required', 'integer', 'between:1,2'],
            'batting_team_id' => [
                'required',
                'integer',
                Rule::in([$fixture->home_team_id, $fixture->away_team_id]),
            ],
            'striker' => ['required', 'string', 'max:100'],
            'non_striker' => [
                'required', 'string', 'max:100', 'different:striker',
            ],
            'current_bowler' => ['required', 'string', 'max:100'],
        ]);

        $battingId = (int) $data['batting_team_id'];
        $bowlingId = $battingId === (int) $fixture->home_team_id
            ? (int) $fixture->away_team_id
            : (int) $fixture->home_team_id;

        DB::transaction(function () use ($fixture, $data, $battingId, $bowlingId) {
            LiveInnings::where('fixture_id', $fixture->id)
                ->lockForUpdate()
                ->get();

            $existing = LiveInnings::where('fixture_id', $fixture->id)
                ->where('innings_number', $data['innings_number'])
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->deliveries()->exists()) {
                abort(409, 'This innings already contains deliveries and cannot be restarted.');
            }

            LiveInnings::where('fixture_id', $fixture->id)
                ->update(['is_current' => false]);

            LiveInnings::updateOrCreate(
                [
                    'fixture_id' => $fixture->id,
                    'innings_number' => $data['innings_number'],
                ],
                [
                    'batting_team_id' => $battingId,
                    'bowling_team_id' => $bowlingId,
                    'striker' => trim($data['striker']),
                    'non_striker' => trim($data['non_striker']),
                    'current_bowler' => trim($data['current_bowler']),
                    'total_runs' => 0,
                    'wickets' => 0,
                    'legal_balls' => 0,
                    'extras' => 0,
                    'is_current' => true,
                    'free_hit_next' => false,
                ]
            );
        });

        return back()->with('status', 'Innings started successfully.');
    }

    public function ball(Request $request, Fixture $fixture)
    {
        $data = $request->validate([
            'innings_id' => ['required', 'integer'],
            'striker' => ['required', 'string', 'max:100'],
            'non_striker' => [
                'required', 'string', 'max:100', 'different:striker',
            ],
            'bowler' => ['required', 'string', 'max:100'],
            'batter_runs' => ['required', 'integer', 'between:0,6'],
            'extra_type' => [
                'required',
                Rule::in(['none', 'wide', 'no_ball', 'bye', 'leg_bye', 'penalty']),
            ],
            'extra_runs' => ['required', 'integer', 'between:0,7'],
            'is_wicket' => ['nullable', 'boolean'],
            'wicket_type' => [
                'nullable',
                Rule::in([
                    'bowled', 'caught', 'lbw', 'run out', 'stumped',
                    'hit wicket', 'retired hurt', 'retired out',
                    'obstructing the field', 'hit the ball twice',
                    'handled the ball',
                ]),
            ],
            'dismissed_batter' => ['nullable', 'string', 'max:100'],
            'incoming_batter' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $isWicket = $request->boolean('is_wicket');
        $extraType = $data['extra_type'];
        $runs = (int) $data['batter_runs'];
        $extras = (int) $data['extra_runs'];

        if (in_array($extraType, ['wide', 'no_ball'], true) && $extras < 1) {
            $extras = 1;
        }

        if ($extraType === 'wide' && $runs > 0) {
            return back()->withErrors([
                'batter_runs' => 'For wides, record completed runs in the extras field.',
            ])->withInput();
        }

        if ($isWicket && (empty($data['dismissed_batter']) || empty($data['wicket_type']))) {
            return back()->withErrors([
                'is_wicket' => 'Choose the dismissed batter and dismissal type.',
            ])->withInput();
        }

        DB::transaction(function () use (
            $fixture, $data, $isWicket, $extraType, $runs, $extras
        ) {
            $innings = LiveInnings::where('fixture_id', $fixture->id)
                ->whereKey($data['innings_id'])
                ->where('is_current', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($innings->wickets >= 10) {
                abort(409, 'This innings has already lost ten wickets.');
            }

            if ((int) $innings->legal_balls >= 120) {
                abort(409, 'The 20-over innings is complete.');
            }

            $striker = trim($data['striker']);
            $nonStriker = trim($data['non_striker']);
            $bowler = trim($data['bowler']);

            $isLegal = !in_array($extraType, ['wide', 'no_ball'], true);
            $freeHitBefore = (bool) $innings->free_hit_next;

            $wicketType = $data['wicket_type'] ?? null;
            $dismissed = $data['dismissed_batter'] ?? null;

            if ($freeHitBefore && $isWicket && !in_array(
                strtolower((string) $wicketType),
                ['run out', 'obstructing the field', 'retired out'],
                true
            )) {
                abort(422, 'A free hit does not permit this dismissal. Record a legal outcome instead.');
            }

            $isNoBall = $extraType === 'no_ball';
            $nextFreeHit = $isNoBall || ($freeHitBefore && !$isLegal);

            $delivery = $innings->deliveries()->create([
                'ball_number' => ((int) $innings->deliveries()->max('ball_number')) + 1,
                'striker' => $striker,
                'non_striker' => $nonStriker,
                'bowler' => $bowler,
                'batter_runs' => $runs,
                'extra_runs' => $extras,
                'extra_type' => $extraType,
                'is_wicket' => $isWicket,
                'wicket_type' => $isWicket ? $wicketType : null,
                'dismissed_batter' => $isWicket ? $dismissed : null,
                'incoming_batter' => $data['incoming_batter'] ?? null,
                'counts_as_legal_ball' => $isLegal,
                'free_hit_before' => $freeHitBefore,
                'note' => $data['note'] ?? null,
            ]);

            $innings->total_runs += $runs + $extras;
            $innings->extras += $extras;

            if ($isLegal) {
                $innings->legal_balls++;
            }

            $countsAsWicket = $isWicket && !in_array(
                strtolower((string) $wicketType),
                ['retired hurt', 'obstructing the field'],
                true
            );

            if ($countsAsWicket) {
                $innings->wickets = min(10, $innings->wickets + 1);
            }

            // For strike rotation, count batter runs plus completed byes/leg-byes.
            $rotationRuns = $runs;

            if (in_array($extraType, ['bye', 'leg_bye'], true)) {
                $rotationRuns += $extras;
            }

            if ($extraType === 'wide') {
                // Wide penalty is one; remaining wides are treated as completed runs.
                $rotationRuns += max(0, $extras - 1);
            }

            if ($rotationRuns % 2 === 1) {
                [$striker, $nonStriker] = [$nonStriker, $striker];
            }

            $overEnded = $isLegal && ((int) $innings->legal_balls % 6 === 0);

            if ($overEnded) {
                [$striker, $nonStriker] = [$nonStriker, $striker];
            }

            if ($isWicket && $countsAsWicket && $data['dismissed_batter']) {
                $dismissedName = trim($data['dismissed_batter']);

                if ($dismissedName === $striker) {
                    if (!empty($data['incoming_batter'])) {
                        $striker = trim($data['incoming_batter']);
                    } elseif ($innings->wickets < 10) {
                        abort(422, 'Enter the incoming batter to complete this wicket.');
                    }
                } elseif ($dismissedName === $nonStriker) {
                    if (!empty($data['incoming_batter'])) {
                        $nonStriker = trim($data['incoming_batter']);
                    } elseif ($innings->wickets < 10) {
                        abort(422, 'Enter the incoming batter to complete this wicket.');
                    }
                }
            }

            $innings->striker = $striker;
            $innings->non_striker = $nonStriker;
            $innings->current_bowler = $bowler;
            $innings->free_hit_next = $nextFreeHit;
            $delivery->striker_after = $striker;
            $delivery->non_striker_after = $nonStriker;
            $delivery->save();

            $innings->save();
        });

        return back()->with('status', 'Delivery recorded successfully.');
    }

    public function undo(Fixture $fixture, LiveDelivery $delivery)
    {
        DB::transaction(function () use ($fixture, $delivery) {
            $innings = LiveInnings::whereKey($delivery->innings_id)
                ->where('fixture_id', $fixture->id)
                ->where('is_current', true)
                ->lockForUpdate()
                ->firstOrFail();

            $latest = $innings->deliveries()
                ->lockForUpdate()
                ->orderByDesc('ball_number')
                ->first();

            if (!$latest || $latest->id !== $delivery->id) {
                abort(409, 'Only the most recent delivery can be undone.');
            }

            $latest->delete();

            $remaining = $innings->deliveries()->get();

            $runs = 0;
            $extras = 0;
            $wickets = 0;
            $legalBalls = 0;

            foreach ($remaining as $item) {
                $runs += $item->totalRuns();
                $extras += (int) $item->extra_runs;
                $legalBalls += (int) $item->counts_as_legal_ball;

                if ($item->is_wicket && !in_array(
                    strtolower((string) $item->wicket_type),
                    ['retired hurt', 'obstructing the field'],
                    true
                )) {
                    $wickets++;
                }
            }

            $last = $remaining->last();

            $innings->total_runs = $runs;
            $innings->extras = $extras;
            $innings->wickets = min(10, $wickets);
            $innings->legal_balls = $legalBalls;

            if ($last) {
                $innings->striker = $last->striker_after;
                $innings->non_striker = $last->non_striker_after;
                $innings->current_bowler = $last->bowler;
                $innings->free_hit_next = $last->extra_type === 'no_ball';
            } else {
                $innings->free_hit_next = false;
            }

            $innings->save();
        });

        return back()->with('status', 'Latest delivery undone.');
    }
}
