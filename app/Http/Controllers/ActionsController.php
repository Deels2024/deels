<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionsController extends Controller
{
    public function campaignsHtmlByCategory(Request $request): string
    {
        $campaigns = Campaign::query()
                             ->active()
                             ->where('category_id', $request->category)
                             ->limit(8)
                             ->inRandomOrder()
                             ->get();

        $html = '
	 <div class="modal fancy-modal" id="modal" style="display: inline-block"><div class="modal__blocks">
	 <h3 style="position: absolute;top: 25px;">Выбери любую копилку и задонать на мечту:</h3>
	 <button class="fancybox-button fancybox-close-small" type="button" data-fancybox-close="" title="Close"><svg xmlns="http://www.w3.org/2000/svg" version="1" viewBox="0 0 24 24"><path d="M13 12l5-5-1-1-5 5-5-5-1 1 5 5-5 5 1 1 5-5 5 5 1-1z"></path></svg></button>
                <div class="modal__message">
                    Выбери любую копилку для внесения доната
                    <span><img src="/images/action-top-banner/x.png" alt=""/></span>
                </div>';

        $campaigns->each(function(Campaign $campaign) use (&$html) {
            $html .= '<a href="#modal-second" data-id="' . $campaign->id . '" class="open-fancybox modal__block">
                    <div class="modal__flat"><img src="' . $campaign->feature_img_url()->feature_image . '" alt=""/></div>
                    <div class="modal__info">
                        <div class="modal__dream">
                            ' . $campaign->title . '
                        </div>
                        <div class="modal__goal">Цель:  ' . get_amount($campaign->goal) . '</div>
                        <div class="modal__profile">
                            <img src="' . $campaign->user?->avatar() . '" alt=""/>
                            ' . $campaign->user?->name . '
                        </div>
                    </div>
                </a>';
        });

        $html .= '</div></div>
                    <a href="#" data-category="' . $request->category . '" class="modal__refresh"><img src="/images/action-top-banner/refresh.png" alt=""/></a>';

        return $html;
    }

    public function getCampaign(Campaign $campaign)
    {
        $html = ' 
 	 <div class="fancy-modal" id="modal-second" style="display: inline-block">     
 	 <button class="fancybox-button fancybox-close-small" type="button" data-fancybox-close="" title="Close"><svg xmlns="http://www.w3.org/2000/svg" version="1" viewBox="0 0 24 24"><path d="M13 12l5-5-1-1-5 5-5-5-1 1 5 5-5 5 1 1 5-5 5 5 1-1z"></path></svg></button>
                    <div class="modal-second__inner">
                <a href="" class="modal-third-back"><img src="/images/action-top-banner/arrow-left.png" alt=""/></a>
                <div class="modal-second__items">
                    <div class="modal-second__title mobile">' . $campaign->title . '</div>
                    <div class="modal-second__start">
                        <div class="card__slider">
                            <div class="card__cart swiper-container swiper">
                                <div class="card__cart-top swiper-wrapper">
                                <div class="card__images swiper-slide">
                                        <img src="' . $campaign->feature_img_url()->original . '" alt=""/>
                                    </div>';
        /*foreach ($campaign->images as $image) {
            $html .= '<div class="card__images swiper-slide">
                                        <img src="' . $image . '" alt=""/>
                                    </div>';
        }*/
        $html .= '</div>
                                <div class="swiper-pagination"></div>
                            </div>
                            <div class="card__gallery swiper-container swiper">
                                <div class="card__cart-bottom swiper-wrapper">
                                <div class="card__cart-bottom-img swiper-slide">
                                        <img src="' . $campaign->feature_img_url()->original . '" alt=""/>
                                    </div>';
        /* foreach ($campaign->images as $image) {
             $html .= '<div class="card__cart-bottom-img swiper-slide">
                                         <img src="' . $image . '" alt=""/>
                                     </div>';
         }*/

        $html .= '  
                                </div>
                            </div>
                        </div>
                        <div class="modal-second__review">
                            <div class="modal-second__review-block">
                                <div class="modal-second__review-image">
                                    <img src="' . $campaign->user?->avatar() . '" alt=""/>
                                </div>
                                <div class="modal-second__review-name">' . $campaign->user->name . '</div>
                            </div>
                            <div class="modal-second__review-head">Описание</div>
                            <div class="modal-second__review-info">
                              ' . safe_output($campaign->description) . '
                            </div>
                        </div>
                    </div>
                    <div class="modal-second__item">
                        <div class="modal-second__header">
                            <div class="modal-second__category">' . $campaign->get_category->category_name . '</div>
                            <div class="modal-second__number">№ ' . $campaign->id . '</div>
                        </div>
                        <div class="modal-second__title desk">' . $campaign->title . '</div>
                        <div class="modal-second__intro-block">
                            <div class="modal-second__stat">Статистика копилки</div>
                            <div class="modal-second__progress">
                                <div class="modal-second__to">' . $campaign->percent_raised() . '%</div>
                                <div class="modal-second__from">100%</div>
                                <div class="modal-second__line"></div>
                            </div>
                            <div class="modal-second__blocks">
                                <div class="modal-second__block">
                                    <p>Цель:</p>
                                    <p>' . get_amount($campaign->goal) . '</p>
                                </div>
                                <div class="modal-second__block">
                                    <p>Осталось дней:</p>
                                    <p>∞</p>
                                </div>
                                <div class="modal-second__block">
                                    <p>Спонсоры:</p>
                                    <p>' . $campaign->success_payments->count() . '</p>
                                </div>
                                <div class="modal-second__block">
                                    <p>Финансировано:</p>
                                    <p>' . get_amount($campaign->success_payments->sum('amount')) . '</p>
                                </div>
                            </div>
                            <div class="modal-second__donate">
                                <a href="#modal-third" data-campaign="' . $campaign->title . '" data-id="' . $campaign->id . '" class="open-fancybox modal-second__btn">Внести донат</a>
                                <div class="modal__message">
                                    Внести донат, чтобы участвовать в конкурсе
                                    <span><img src="/images/action-top-banner/x.png" alt=""/></span>
                                </div>
                            </div>
                            <div class="modal-second__btns">
                                <div class="modal-second__copy">Скопировать <img src="/images/action-top-banner/copy.svg" alt=""/></div>
                                <div class="modal-second__life">
                                    <img src="/images/action-top-banner/health-white.svg" alt=""/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
               </div>
            </div>';

        return $html;
    }

    public function getMyActions()
    {
        $payments = Payment::query()
                           ->selectRaw('category_name, category_id, count(*) count')
                           ->join('campaigns', 'campaign_id', 'campaigns.id')
                           ->join('categories', 'category_id', 'categories.id')
            //                           ->where('amount', '>=', 5000)
                           ->where('payments.user_id', Auth::id())
                           ->whereIn('campaigns.category_id', [10, 3, 13])
                           ->groupBy(['category_id', 'category_name'])
                           ->get();

        return view('admin.action_campaigns', compact('payments'));
    }
}