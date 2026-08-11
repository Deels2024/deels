@include('challenges.partials.contest_data', [
    'contest' => $challenge,
    'contestType' => 'challenge',
    'contestTitle' => 'челлендж',
    'contestGenitive' => 'челленджа',
    'stopRoute' => 'challenges.stop',
    'editRoute' => 'challenges.edit',
    'backRoute' => 'user_challenges',
])
