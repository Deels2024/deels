
<div class="single-campaign-header">

    <div class="single-campaign-menu">

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @php
                        $backers_count = $campaign->success_payments->count();
                        $updates_count = $campaign->updates->count();
                        $faqs_count = $campaign->faqs->count();
                    @endphp
                    <ul>
                        <li><a href="{{route('campaign_single', $campaign->slug)}}"> <i class="fa fa-home"></i> @lang('app.campaign_home') </a> </li>
                        <li><a href="{{route('campaign_backers', $campaign->slug)}}"> <i class="fa fa-user"></i> @lang('app.backers') ({{$backers_count}}) </a> </li>
                        <li>
                            <a href="{{route('campaign_updates', $campaign->slug)}}"><i class="fa fa-refresh"></i> @lang('app.updates')
                                @if($updates_count > 0) ({{$updates_count}}) @endif
                            </a>
                        </li>
                        <li>
                            <a href="{{route('campaign_faqs', $campaign->slug)}}"> <i class="fa fa-question-circle"></i> @lang('app.faqs')  @if($faqs_count > 0) ({{$faqs_count}}) @endif </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>