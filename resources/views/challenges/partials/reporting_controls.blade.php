@if($reportingState['visible'])
    <div class="contest-reporting__controls {{!empty($compact) ? 'contest-reporting__controls--compact' : ''}}">
        @if($reportingState['checkin'] === 'button')
            <form class="contest-reporting__form js-contest-report-form"
                  action="{{route('contests.reports.store', ['type' => $contestType, 'id' => $contest->id])}}">
                @csrf
                <button class="challenge-btn challenge-btn--fill js-contest-report-button" type="submit"
                        {{$reportingState['button_done'] || !$reportingState['available'] ? 'disabled' : ''}}>
                    Сегодня сделал(а)
                </button>
            </form>
        @elseif($reportingState['checkin'] === 'value')
            <form class="contest-reporting__form js-contest-report-form"
                  action="{{route('contests.reports.store', ['type' => $contestType, 'id' => $contest->id])}}">
                @csrf
                <input class="contest-reporting__input js-contest-report-value" type="number" step="any"
                       name="value" inputmode="decimal" value="{{$reportingState['value']}}"
                       aria-label="Результат" {{$reportingState['available'] ? '' : 'disabled'}}>
                <button class="challenge-btn challenge-btn--fill js-contest-report-submit" type="submit"
                        disabled data-reporting-available="{{$reportingState['available'] ? 1 : 0}}">
                    Отправить
                </button>
            </form>
        @else
            <a class="challenge-btn challenge-btn--fill {{$reportingState['story_allowed'] ? '' : 'disabled'}}"
               @if($reportingState['story_allowed'])
                   href="{{route('stories.create', [$routeParam => $contest->id, 'online_report' => 1])}}"
               @else
                   aria-disabled="true"
               @endif>
                Снять онлайн-сторис
            </a>
        @endif
        @if(!empty($compact))
            <div class="contest-reporting__notice" role="status"></div>
        @endif
    </div>
@endif
