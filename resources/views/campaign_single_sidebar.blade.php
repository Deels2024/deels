<div class="single-donate-wrap">

    <h3 class="campaign-single-sub-title">{!! $campaign->short_description !!}</h3>

    <div class="single-author-box">
        <div class="product__ava">
        <img src="{!! $campaign->user->avatar() !!}" class="magnific_image circle-img">
        </div>
        <p><strong>{!! $campaign->user->fullname !!}</strong> <br/> {!! $campaign->address !!} </p>
    </div>

    @if($campaign->get_category)
        <div class="campaign-tag-list">
            <a href="{{route('single_category', $campaign->get_category->slug)}}"><i class="glyphicon glyphicon-tag"></i> {!! $campaign->get_category->category_name !!}
            </a>
        </div>
    @endif

    <div class="campaign-progress-info">
        <h4>{!! get_amount($campaign->success_payments->sum('amount')) !!}
            <small>@lang('app.raised_of') {!! get_amount($campaign->goal) !!} @lang('app.goal')</small></h4>

        <div class="progress">
            @php
                $percent_raised = $campaign->percent_raised();
            @endphp
            <div class="progress-bar" role="progressbar" aria-valuenow="{!! $percent_raised !!}" aria-valuemin="0" aria-valuemax="100" style="width: {!! $percent_raised <= 100 ? $percent_raised : 100 !!}%;">
                {!! $percent_raised !!}%
            </div>
        </div>

        <ul>
            <li><strong>{!! $campaign->days_left() !!}</strong> @lang('app.days_left')</li>
            <li><strong>{!! $campaign->success_payments->count() !!}</strong> @lang('app.backers')</li>
        </ul>
    </div>

    <hr/>


    <div class="input-group">
        <input type="text" id="campaign_url_input" class="form-control" value="{!! route('campaign_single', $campaign->slug) !!}">
        <div class="input-group-btn">
            <button class="btn hu-btn-filled" id="campaign_url_copy_btn">@lang('app.copy_link')</button>
        </div>
    </div>

    <hr/>

    <div class="socialShareWrap">
        <ul>
            <li><a href="#" class="share s_facebook"><i class="fa fa-facebook-square"></i> </a></li>
            <li><a href="#" class="share s_twitter"><i class="fa fa-twitter-square"></i> </a></li>
            {{--            <li><a href="#" class="share s_plus"><i class="fa fa-google-plus-square"></i> </a> </li>--}}
            <li><a href="#" class="share s_linkedin"><i class="fa fa-linkedin-square"></i> </a></li>
            <li><a href="#" class="share s_vk"><i class="fa fa-vk"></i> </a></li>
            <li><a href="#" class="share s_pinterest"><i class="fa fa-pinterest-square"></i> </a></li>
            {{--            <li><a href="#" class="share s_tumblr"><i class="fa fa-tumblr-square"></i> </a> </li>--}}
            {{--            <li><a href="#" class="share s_delicious"><i class="fa fa-delicious"></i> </a> </li>--}}
            <li><a href="#" class="share s_delicious"> </a></li>
            <li><a href="#" class="share s_delicious"> </a></li>
            <li><a href="#" class="share s_delicious"> </a></li>
            <li><a href="#" class="share s_delicious"> </a></li>
                <li>
                    <a href="#" class="addLike" data-campaign="{{$campaign->id}}">
                        <i class="fa fa-heart"></i>
                        <span>{{$campaign->likes()->count()}}</span>
                    </a>
                </li>
        </ul>
    </div>

    @php
        $is_ended = $campaign->is_ended();
    @endphp

    <div class="donate_form">

        <h2>@lang('app.donate')</h2>

        @if( ! $is_ended)

            <form action="{{route('add_to_cart')}}" class="form-horizontal" method="post"> @csrf

                <input type="hidden" name="campaign_id" value="{!! $campaign->id !!}"/>
                <div class="donate_amount_field">
                    <div class="donate_currency">{!! get_currency_symbol(get_option('currency_sign')) !!}</div>
                    <input type="number" step="1" min="1" name="amount" class="form-control" value="{!! $campaign->recommended_amount !!}" placeholder="@lang('app.enter_amount')"/>
                </div>

                @if($campaign->amount_prefilled())
                    <div class="donate-amount-placeholder">
                        @foreach($campaign->amount_prefilled() as $amount_prefield)
                            <a href="javascript:;" class="donate-amount-prefill" data-value="{!! $amount_prefield !!}">{!! get_amount($amount_prefield) !!}</a>
                        @endforeach
                    </div>
                @endif

                <div class="donate-form-button">
                    <button type="submit" class="btn hu-btn-filled btn-block btn-lg">@lang('app.donate')</button>
                </div>
            </form>

        @else
            <div class="alert alert-warning">
                <h5>@lang('app.campaign_has_been_ended')</h5>
            </div>
        @endif

    </div>


    @if($campaign->rewards->count() > 0)
        <div class="rewards-wrap">

            <h2>@lang('app.or_choose_a_reward')</h2>

            @foreach($campaign->rewards as $reward)

                @php $claimed_count = $reward->payments->count(); @endphp

                <div class="reward-box @if($reward->quantity <= $claimed_count || $is_ended ) reward-disable @endif ">
                    @if($reward->quantity > $claimed_count )
                        <a href="{!! route('add_to_cart', $reward->id) !!}">
                            <span class="reward-amount">@lang('app.pledge') <strong>{!! get_amount($reward->amount) !!}</strong></span>
                            <span class="reward-text">{!! $reward->description !!}</span>
                            <span class="reward-claimed-count"> {!! $claimed_count !!} @lang('app.claimed_so_far') {!! $reward->quantity !!} </span>
                            <span class="reward-estimated-delivery"> @lang('app.estimated_delivery'): {!! date('F Y', strtotime($reward->estimated_delivery)) !!}</span>

                            @if($reward->digital_downloads)
                                @php
                                    $reward_media = get_media($reward->digital_downloads);
                                @endphp

                                @if($reward_media)
                                    <p><i class="fa fa-cloud-download"></i> @lang('app.digital_downloads_available') </p>
                                @endif
                            @endif

                            <span class="reward-button"> @lang('app.select_reward') </span>
                        </a>

                    @else
                        <span class="reward-amount">@lang('app.pledge') <strong>{!! get_amount($reward->amount) !!}</strong></span>
                        <span class="reward-text">{!! $reward->description !!}</span>
                        <span class="reward-claimed-count"> {!! $claimed_count !!} @lang('app.claimed_so_far') {!! $reward->quantity !!} </span>
                        <span class="reward-estimated-delivery"> @lang('app.estimated_delivery'): {!! date('F Y', strtotime($reward->estimated_delivery)) !!}</span>

                        @if($reward->digital_downloads)
                            @php
                                $reward_media = get_media($reward->digital_downloads);
                            @endphp

                            @if($reward_media)
                                <p><i class="fa fa-cloud-download"></i> @lang('app.digital_downloads_available') </p>
                            @endif
                        @endif

                        <span class="reward-button"> @lang('app.sold_out') </span>
                    @endif
                </div>
            @endforeach

        </div>
    @endif

</div>



@section('page-js')

    <script src="{{asset('assets/plugins/SocialShare/SocialShare.min.js')}}"></script>
    <script>
		$('.share').ShareLink({
			title: '{{$campaign->title}}', // title for share message
			text: '{{$campaign->short_description ? $campaign->short_description : $campaign->description}}', // text for share message
			width: 640, // optional popup initial width
			height: 480 // optional popup initial height
		})
    </script>

    <script>
		$(function () {
			$(document).on('click', '.donate-amount-prefill', function (e) {
				$(this).closest('form').find($('[name="amount"]')).val(Number($(this).data('value')).toFixed(2));
			});

			$('#campaign_url_copy_btn').click(function () {
				copyToClipboard('#campaign_url_input');
			});
		});

		function copyToClipboard(element) {
			var $temp = $("<input>");
			$("body").append($temp);
			$temp.val($(element).val()).select();
			document.execCommand("copy");
			toastr.success('@lang('app.campaign_url_copied')', '@lang('app.success')', toastr_options);
			$temp.remove();
		}

    </script>


    <script>
		$(document).on('click', '.comments-list .fa-reply', function (e) {
			e.preventDefault();

			var comment_id = $(this).closest('.comment-box').attr('data-comment-id');
			var reply_form = $('.post-comments-form').html();
			reply_form += '<a href="javascript:;" class="text-danger reply_form_remove"><i class="fa fa-times"> </a>';

			//reply_form_box
			$(this).closest('.comment-box').find('.reply_form_box').html(reply_form).show().find('.comment_id').val(comment_id);

		});

		$(document).on('click', '.reply_form_remove', function (e) {
			e.preventDefault();
			$(this).closest('form').remove();
			$(this).closest('.reply_form_box').hide();
		});
    </script>
@endsection
