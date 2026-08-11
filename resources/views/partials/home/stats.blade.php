<div class="info">
    <div class="container">
        <div class="info__content">
            <div class="info__img">
                <img class="lozad" src="/img/info-img.jpg" width="200">
            </div>
            <div style="width: 100%">
                <div class="info-list mb-4">
                    <div class="info__item">
                        <div class="info-item__num">{{number_format($campaignsCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">всего копилок</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num">{{number_format($usersCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">всего спонсоров</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num">{!! number_format(round($fundRaised+569600), 0, ',', ',') !!}<span class="ruble-sign">₽</span></div>
                        {{--                        <div class="info-item__num">569600<span class="ruble-sign">₽</span></div>--}}
                        <div class="info-item__text">привлеченные средства</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num ">{{number_format($fundedCampaignsCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">{{trans_choice('numbers.founded_campaigns', $fundedCampaignsCount)}}</div>
                    </div>
                </div>
                <div class="info-list">
                    <div class="info__item">
                        <div class="info-item__num">{{number_format($storiesCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">всего сторис</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num">{{number_format($storiesDonatedCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">задоначено дилсов</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num">{{number_format($storiesViewsCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">раз просмотрены сторис</div>
                    </div>
                    <div class="info__item">
                        <div class="info-item__num ">{{number_format($storiesCommentsCount, 0, ',', ',')}}</div>
                        <div class="info-item__text">комментариев в сторис</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>