@include('challenges.partials.contest_data', [
    'contest' => $battle,
    'contestType' => 'battle',
    'contestTitle' => 'батл',
    'contestGenitive' => 'батла',
    'stopRoute' => 'battles.stop',
    'editRoute' => 'battles.edit',
    'backRoute' => 'admin_battles',
])
