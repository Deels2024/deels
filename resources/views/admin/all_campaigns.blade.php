@extends('layouts.admin.app_neon')

@push('after_content')
    <style>
        .admin-table .actions-link[data-toggle="tooltip"] {
            position: relative;
        }

        .admin-table .actions-link[data-toggle="tooltip"]::before,
        .admin-table .actions-link[data-toggle="tooltip"]::after {
            pointer-events: none;
            position: absolute;
            left: 50%;
            opacity: 0;
            transform: translate(-50%, 6px);
            transition: opacity .15s ease, transform .15s ease;
            z-index: 20;
        }

        .admin-table .actions-link[data-toggle="tooltip"]::before {
            content: attr(title);
            bottom: calc(100% + 8px);
            width: max-content;
            max-width: 220px;
            padding: 5px 8px;
            border-radius: 4px;
            background: rgba(34, 34, 34, .95);
            color: #fff;
            font-size: 12px;
            line-height: 1.3;
            white-space: normal;
        }

        .admin-table .actions-link[data-toggle="tooltip"]::after {
            content: "";
            bottom: calc(100% + 3px);
            border: 5px solid transparent;
            border-top-color: rgba(34, 34, 34, .95);
        }

        .admin-table .actions-link[data-toggle="tooltip"]:hover::before,
        .admin-table .actions-link[data-toggle="tooltip"]:hover::after,
        .admin-table .actions-link[data-toggle="tooltip"]:focus::before,
        .admin-table .actions-link[data-toggle="tooltip"]:focus::after {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .admin-table .actions-link_disabled {
            background-color: #bdbdbd;
            cursor: not-allowed;
            filter: grayscale(1);
            opacity: .55;
        }
    </style>
@endpush


@section('content')
    {{--    @if (request()->has('new_design'))--}}
    <main class="admin-main">
        <div class="account-main__head">
            <div class="account-main__head-side">
                <h1 class="account-main__title">{{$title}}</h1><span>Общее: 20</span>
            </div>
            <div class="account-main__head-side"><a class="btn btn_fill d-flex ai-center download_excel" href="?{{request()->query->count() ? http_build_query(request()->query->all()).'&excel=1' : 'excel=1'}}">
                    <svg class="mr-1" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6667 1.66675H5.00001C4.55798 1.66675 4.13406 1.84234 3.8215 2.1549C3.50894 2.46746 3.33334 2.89139 3.33334 3.33341V16.6667C3.33334 17.1088 3.50894 17.5327 3.8215 17.8453C4.13406 18.1578 4.55798 18.3334 5.00001 18.3334H15C15.442 18.3334 15.866 18.1578 16.1785 17.8453C16.4911 17.5327 16.6667 17.1088 16.6667 16.6667V6.66675L11.6667 1.66675Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.6667 1.66675V6.66675H16.6667" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 15V10" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.5 12.5H12.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Скачать Excel файл
                </a></div>
        </div>
{{--        <button class="aside-nav-btn">Открыть меню</button>--}}
        <form class="form form--admin" action="{{route('campaign_admin_search')}}">
            <div class="form-row">
                <div class="form-field form-field--6">
                    <input type="date" name="date_from" value="{{request('date_from')}}">
                </div>
                <div class="form-field form-field--6">
                    <input type="date" name="date_to" value="{{request('date_to')}}">
                </div>
            </div>
            <div class="form-field">
                <input type="text" name="q" value="{{request('q')}}" placeholder="Ключевые слова копилки">
            </div>
            <div class="form-row">
                <div class="form-field form-field--6">
                    <input type="number" name="sumFrom" value="{{request('sumFrom')}}" class="form-control mb-2" placeholder="Сумма от">
                </div>
                <div class="form-field form-field--6">
                    <input type="number" name="sumTo" value="{{request('sumTo')}}" class="form-control mb-2" placeholder="Сумма до">
                </div>
            </div>
            <label class="d-flex ai-center mt-1 mb-2">
                <input class="mr-1" type="checkbox" name="paidFrom" placeholder="Внесено от" value="{{request('paidFrom')}}"><span>Пополненные</span>
            </label>
            @if(isset($show_ai_moderated))
            <label class="d-flex ai-center mt-1 mb-2">
                <input class="mr-1" type="checkbox" name="ai_moderated" value="1" {{isset($_GET['ai_moderated']) ? 'checked' : ''}}><span>Отклонено ИИ</span>
            </label>
            @endif
            <button class="form__btn btn btn_fill">Поиск</button>
        </form>
        @if($campaigns->count() > 0)
            <div class="admin-table">
                <table>
                    <thead>
                    <tr>
                        <th>№ копилки</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Информация о владельце</th>
                        <th>Email владельца</th>
                        <th>Порядок в слайдере</th>
                        <th>Лайков</th>
                        <th>Действие</th>
                        <th>Дата/время</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($campaigns as $campaign)
                        @php
                            $canApprove = in_array($campaign->status, [0, 2], true);
                            $canBlock = in_array($campaign->status, [0, 1], true);
                        @endphp
                        <tr>
                            <td>
                                <a href="{{route('campaign_single', $campaign->slug)}}" target="_blank">#{{$campaign->id}}</a>
                            </td>
                            <td><a href="{{route('campaign_single', $campaign->slug)}}" target="_blank"><img src="{{$campaign->feature_img_url()->thumbnail}}" alt=""/></a></td>
                            <td>
                                <a href="{{route('campaign_single', $campaign->slug)}}" target="_blank">
                                    {{$campaign->title}}
                                    @if($campaign->getReasons())
                                        <div style="padding: 5px 10px; font-size: 12px; color: #ff0000;background-color: rgba(68, 68, 68, 0.9);">
                                            {!! $campaign->getReasons() !!}
                                        </div>
                                    @endif
                                </a>
                            </td>
                            <td>
                                <p>{{$campaign->user->fullname}}</p>
                            </td>
                            <td>
                                <p>{{$campaign->user->email}}</p>
                            </td>
                            <td>
                                <div class="input-num">
                                    <button class="input-num__add"></button>
                                    <input class="sliderOrder" type="number" value="{{$campaign->slider_order ?? 0}}" maxlength="5" min="0" inputmode="numeric" data-id="{{$campaign->id}}"/>
                                    <button class="input-num__remove"></button>
                                </div>
                            </td>
                            <td>
                                <p>{{$campaign->likes()->count()}}</p>
                            </td>
                            <td>
                                <div class="d-flex fd-column">
                                    <div class="actions mb-1">
{{--                                        <a class="actions-link" href="{{route('campaign_single', $campaign->slug)}}" style="background-image: url(/dist/images/admin_icons/icon-eye.svg)" data-toggle="tooltip"  title="Просмотр"></a>--}}
                                        <a class="actions-link" href="{{route('edit_campaign', $campaign->id)}}" style="background-image: url(/dist/images/admin_icons/icon-edit.svg)" data-toggle="tooltip"  title="Редактировать"></a>
                                        @if($canApprove)
                                            <a class="actions-link" href="{{route('campaign_status', [$campaign->id, 'approve'])}}" style="background-image: url(/dist/images/admin_icons/icon-add.svg)" data-toggle="tooltip"  title="Одобрить"></a>
                                        @else
                                            <span class="actions-link actions-link_disabled" style="background-image: url(/dist/images/admin_icons/icon-add.svg)" data-toggle="tooltip" title="Одобрить" aria-disabled="true"></span>
                                        @endif
                                        @if($canBlock)
                                            <a class="actions-link" href="{{route('campaign_status', [$campaign->id, 'block'])}}" style="background-image: url(/dist/images/admin_icons/icon-block.svg)" data-toggle="tooltip"  title="Отклонить"></a>
                                        @else
                                            <span class="actions-link actions-link_disabled" style="background-image: url(/dist/images/admin_icons/icon-block.svg)" data-toggle="tooltip" title="Отклонить" aria-disabled="true"></span>
                                        @endif
                                        <a class="actions-link" href="{{route('campaign_delete', $campaign->id)}}" style="background-image: url(/dist/images/admin_icons/icon-del.svg)" data-toggle="tooltip"  title="Удалить"></a>

{{--                                        <a class="actions-link" href="{{route('campaign_status', [$campaign->id, 'feature'])}}" style="background-image: url(/dist/images/admin_icons/icon-fav.svg)"  data-toggle="tooltip"  title="Продвинуть"></a>--}}
                                    </div>
                                    {{--                                <a class="button button--accent" href="#">В слайдер</a>--}}
                                </div>
                            </td>
                            <td>
                                <p>{{$campaign->created_at->format('d.m.Y H:i')}}</p>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            {!! $campaigns->withQueryString()->links() !!}
    </main>

    {{--    @else--}}

    {{--        <div class="dashboard-wrap">--}}
    {{--            <div class="container">--}}
    {{--                <div id="wrapper">--}}

    {{--                    @include('admin.menu')--}}

    {{--                    <div id="page-wrapper">--}}

    {{--                        @if( ! empty($title))--}}
    {{--                            <div class="row">--}}
    {{--                                <div class="col-lg-12">--}}
    {{--                                    <h1 class="page-header"> {{ $title }}  </h1>--}}
    {{--                                </div> <!-- /.col-lg-12 -->--}}
    {{--                            </div> <!-- /.row -->--}}
    {{--                        @endif--}}

    {{--                        @include('admin.flash_msg')--}}

    {{--                        <div class="admin-campaign-lists-sub-head">--}}
    {{--                            <div class="row">--}}
    {{--                                <div class="col-md-5">--}}
    {{--                                    @lang('app.total') : {{$campaigns->count()}}--}}
    {{--                                </div>--}}

    {{--                                <div class="col-md-7">--}}
    {{--                                    <form method="get" action="{{route('campaign_admin_search')}}">--}}
    {{--                                        <div class="form-row align-items-center float-right">--}}
    {{--                                            <div class="col-auto">--}}
    {{--                                                <input type="text" name="q" value="{{request('q')}}" class="form-control mb-2" placeholder="@lang('app.campaign_title_keyword')">--}}
    {{--                                            </div>--}}

    {{--                                            <div class="col-auto">--}}
    {{--                                                Пополненные--}}
    {{--                                                <input type="checkbox" name="paidFrom" value="{{request('paidFrom')}}" placeholder="Внесено от">--}}
    {{--                                            </div>--}}

    {{--                                            @if (\Illuminate\Support\Facades\Route::currentRouteAction()==='App\Http\Controllers\CampaignsController@allCampaigns')--}}
    {{--                                                <div class="col-auto">--}}
    {{--                                                    <input style="display: inline-block; width: 49%" type="number" name="sumFrom" value="{{request('sumFrom')}}" class="form-control mb-2" placeholder="Сумма от">--}}
    {{--                                                    <input style="display: inline-block; width: 49%" type="number" name="sumTo" value="{{request('sumTo')}}" class="form-control mb-2" placeholder="Сумма до">--}}
    {{--                                                </div>--}}
    {{--                                            @endif--}}

    {{--                                            <div class="col-auto">--}}
    {{--                                                <button type="submit" class="btn btn-primary mb-2">@lang('app.search')</button>--}}
    {{--                                            </div>--}}
    {{--                                        </div>--}}
    {{--                                    </form>--}}

    {{--                                </div>--}}
    {{--                            </div>--}}
    {{--                        </div>--}}

    {{--                        @if($campaigns->count() > 0)--}}
    {{--                            <table class="table table-striped table-bordered table-responsive">--}}

    {{--                                <tr>--}}
    {{--                                    <th width="70">@lang('app.image')</th>--}}
    {{--                                    <th>@lang('app.title')</th>--}}
    {{--                                    <th>@lang('app.owner_info')</th>--}}
    {{--                                    <th>Email владельца</th>--}}
    {{--                                    <th>Порядок в слайдере</th>--}}
    {{--                                    <th>Лайков</th>--}}
    {{--                                    <th>Комментариев</th>--}}
    {{--                                    <th>@lang('app.actions')</th>--}}
    {{--                                </tr>--}}

    {{--                                @foreach($campaigns as $campaign)--}}

    {{--                                    <tr onclick="$(this).next().slideToggle();">--}}
    {{--                                        <td><img src="{{$campaign->feature_img_url()->thumbnail}}" class="img-fluid"/></td>--}}
    {{--                                        <td>{{$campaign->title}}--}}

    {{--                                            @if($campaign->is_funded == 1)--}}
    {{--                                                <p class="bg-success text-white p-1">@lang('app.added_to_funded')</p>--}}
    {{--                                            @endif--}}
    {{--                                        </td>--}}

    {{--                                        <td>--}}
    {{--                                            <strong>{{$campaign->user->name}}</strong> <br/>--}}
    {{--                                            <strong>{{$campaign->user->email}}</strong> <br/>--}}
    {{--                                            @lang('app.address') : {{$campaign->address}}--}}
    {{--                                        </td>--}}
    {{--                                        <td>--}}
    {{--                                            <input type="number" class="sliderOrder form-control" data-id="{{$campaign->id}}" value="{{$campaign->slider_order ?? 0}}">--}}
    {{--                                        </td>--}}
    {{--                                        <td>{{$campaign->likes()->count()}}</td>--}}
    {{--                                        <td>{{\App\Comment::approved()->whereCampaignId($campaign->id)->with('childs_approved')->count()}}</td>--}}
    {{--                                        <td>--}}
    {{--                                            <a href="{{route('campaign_single', $campaign->slug)}}" class="btn btn-primary btn-sm" data-toggle="tooltip" title="View"><i class="fa fa-eye"></i>--}}
    {{--                                            </a>--}}
    {{--                                            <a href="{{route('edit_campaign', $campaign->id)}}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i>--}}
    {{--                                            </a>--}}
    {{--                                            @if($campaign->status == 0)--}}
    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'approve'])}}" class="btn btn-success btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-check-circle-o"></i>--}}
    {{--                                                </a>--}}
    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'block'])}}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i>--}}
    {{--                                                </a>--}}

    {{--                                            @elseif($campaign->status == 1)--}}

    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'block'])}}" class="btn btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.block')"><i class="fa fa-ban"></i>--}}
    {{--                                                </a>--}}

    {{--                                            @elseif($campaign->status == 2)--}}
    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'approve'])}}" class="btn btn-success btn-sm" data-toggle="tooltip" title="@lang('app.approve')"><i class="fa fa-check-circle-o"></i>--}}
    {{--                                                </a>--}}
    {{--                                            @endif--}}

    {{--                                            @if(request()->segment(3) == 'expired_campaigns')--}}
    {{--                                                @if($campaign->is_funded != 1)--}}
    {{--                                                    <a href="{{route('campaign_status', [$campaign->id, 'funded'])}}" class="btn btn-info btn-sm" data-toggle="tooltip" title="@lang('app.mark_as_funded')"><i class="fa fa-check-circle-o"></i> @lang('app.mark_as_funded')--}}
    {{--                                                    </a>--}}
    {{--                                                @endif--}}
    {{--                                            @endif--}}

    {{--                                            <a href="{{route('campaign_delete', $campaign->id)}}" class="btn btn-delete btn-danger btn-sm" data-toggle="tooltip" title="@lang('app.delete')"><i class="fa fa-trash-o"></i>--}}
    {{--                                            </a>--}}

    {{--                                            @if($campaign->is_staff_picks != 1)--}}
    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'add_staff_picks'])}}" class="btn btn-info btn-sm" data-toggle="tooltip" title="@lang('app.add_staff_picks')"><i class="fa fa-plus-square-o"></i> @lang('app.add_staff_picks')--}}
    {{--                                                </a>--}}

    {{--                                            @else--}}
    {{--                                                <a href="{{route('campaign_status', [$campaign->id, 'remove_staff_picks'])}}" class="btn btn-warning btn-sm" data-toggle="tooltip" title="@lang('app.remove_staff_picks')"><i class="fa fa-minus-square-o"></i> @lang('app.remove_staff_picks')--}}
    {{--                                                </a>--}}
    {{--                                            @endif--}}

    {{--                                            <a href="{{route('campaign_status', [$campaign->id, 'feature'])}}" class="btn btn-{{!$campaign->is_feature?'outline-':''}}secondary" data-toggle="tooltip" title="{{$campaign->is_feature? __('app.added_to_feature') : __('app.add_to_feature')}}">--}}
    {{--                                                <i class="fa fa-bookmark"></i> {{$campaign->is_feature? __('app.added_to_feature') : __('app.add_to_feature')}}--}}
    {{--                                            </a>--}}


    {{--                                        </td>--}}

    {{--                                    </tr>--}}
    {{--                                    <tr style="display: none">--}}
    {{--                                        <td colspan="7">--}}
    {{--                                            <table class="table table-striped table-bordered table-responsive">--}}
    {{--                                                @foreach($campaign->backers() as $user)--}}
    {{--                                                    <tr>--}}
    {{--                                                        <td>{{$user->id}}</td>--}}
    {{--                                                        <td>{{$user->name}}</td>--}}
    {{--                                                        <td>{{$user->email}}</td>--}}
    {{--                                                        <td>{{\App\Payment::where('campaign_id', $campaign->id)->where('user_id', $user->id)->where('status', 'success')->sum('amount')}}--}}
    {{--                                                            р.--}}
    {{--                                                        </td>--}}
    {{--                                                    </tr>--}}
    {{--                                                @endforeach--}}
    {{--                                            </table>--}}
    {{--                                        </td>--}}
    {{--                                    </tr>--}}
    {{--                                @endforeach--}}

    {{--                            </table>--}}

    {{--                            {!! $campaigns->withQueryString()->links() !!}--}}
    {{--                        @else--}}
    {{--                            <div class="no-data-wrap text-center p-5 mt-5">--}}
    {{--                                <i class="fa fa-frown-o"></i>--}}
    {{--                                <h1>@lang('app.no_campaigns_to_display')</h1>--}}
    {{--                            </div>--}}
    {{--                        @endif--}}

    {{--                        <div class="clearfix"></div>--}}
    {{--                    </div>--}}

    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    @endif--}}
@endsection

@section('page-js')
    <script type="text/javascript">
		$(document).ready(function () {
			$('a.btn-danger[href*="block"]').click(function (e) {
				e.preventDefault();
				let reason = prompt('Укажите причину блокировки', 'Неуместный или нежелательный контент');
				window.location = $(this).attr('href') + '?reason=' + reason;
			});

			$('.btn-delete').click(function (e) {
				if (!confirm("@lang('app.are_you_sure_undone')")) {
					e.preventDefault();
				}
			});
			$('.sliderOrder').change(function () {
				$.post('/dashboard/campaigns/' + $(this).data('id') + '/sliderOrder', {order: $(this).val()});
			});
		});
    </script>
@endsection
