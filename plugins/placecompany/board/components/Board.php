<?php namespace Placecompany\Board\Components;

use Backend\Facades\BackendAuth;
use Cms\Classes\ComponentBase;
use Cms\Classes\Controller;
use Illuminate\Support\Facades\Lang;
use JavaScript;
use October\Rain\Support\Facades\Flash;
use Placecompany\Board\Classes\BoardBuilderManager;
use Placecompany\Board\Classes\BoardContentManager;
use Placecompany\Board\Classes\BoardContentMediaManager;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\BoardSkinManager;
use Placecompany\Board\Classes\BoardUrlManager;
use Placecompany\Board\Classes\BoardVoteManager;
use Placecompany\Board\Classes\Helpers;
use Placecompany\Board\Models\BoardVote;
use Placecompany\Board\Models\Settings;
use Request;
use Validator;
use ValidationException;
use Db;

class Board extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Board',
            'description' => '게시판 mod 에 맞게 view 를 생성합니다.'
        ];
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title'             => 'Board ID',
                'description'       => '게시판 아이디',
                'type'              => 'string',
                'default'           => false,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ]
        ];
    }

    public function init()
    {
        $this->addComponent('Placecompany\BoardComment\Components\BoardComment', 'BoardComment', []);
    }

    public function onRun()
    {
        // KBoard 미디어 추가 스타일 속성 등록
        $this->addCss('template/css/editor_media.css', 'board');

        // 활성화된 스킨의 style.css 등록
        $skin = BoardSkinManager::getInstance();
        foreach($skin->getActiveList() as $skin_name){
            $this->addCss($skin->url($skin_name, 'style.css'),'board');
        }

        $this->addJs('template/js/script.js', 'board');

        // Tags Input 등록
        $this->addCss('assets/plugins/tagsinput/jquery.tagsinput.css', 'board');
        $this->addJs('assets/plugins/tagsinput/jquery.tagsinput.js', 'board');

        // Moment.js 등록
        $this->addJs('assets/plugins/moment/moment.js', 'board');

        // jQuery Date Range Picker Plugin 등록
        $this->addCss('assets/plugins/daterangepicker/daterangepicker.css', 'board');
        $this->addJs('assets/plugins/daterangepicker/jquery.daterangepicker.js', 'board');

        // jQuery lightSlider 등록
        $this->addCss('assets/plugins/lightslider/css/lightslider.css', 'board');
        $this->addJs('assets/plugins/lightslider/js/lightslider.js', 'board');

        // 구글 리캡차 등록
        $this->addJs('https://www.google.com/recaptcha/api.js', 'board');

        // 설정 등록
        $script = JavaScript::put([
            'board_settings' => [
                'version' => '1.0.0',
                'home_url' => \Url::to('/'),
                'site_url' => \Url::to('/'),
                'post_url' => \Url::to('/board-post'),
                'plugin_url' => url('plugins/placecompany/board'),
                'media_group' => Helpers::board_media_group(),
                'view_iframe' => Helpers::board_view_iframe(),
                'locale' => \App::getLocale()
            ],
            'board_localize_strings' => [
                'board_add_media' => Lang::get('placecompany.board::lang.Board add media'),
                'next' => Lang::get('placecompany.board::lang.Next'),
                'prev' => Lang::get('placecompany.board::lang.Prev'),
                'required' => Lang::get('placecompany.board::lang.board.required', [
                    'title' => '%s'
                ]),
                'please_enter_the_title' => Lang::get('placecompany.board::lang.Please enter the title.'),
                'please_enter_the_author' => Lang::get('placecompany.board::lang.Please enter the author.'),
                'please_enter_the_password' => Lang::get('placecompany.board::lang.Please enter the password.'),
                'please_enter_the_CAPTCHA' => Lang::get('placecompany.board::lang.Please enter the captcha.'),
                'please_enter_the_name' => Lang::get('placecompany.board::lang.Please enter the name.'),
                'please_enter_the_email' => Lang::get('placecompany.board::lang.Please enter the email.'),
                'you_have_already_voted' => Lang::get('placecompany.board::lang.You have already voted.'),
                'please_wait' => Lang::get('placecompany.board::lang.Please wait.'),
                'newest' => Lang::get('placecompany.board::lang.Newest'),
                'best' => Lang::get('placecompany.board::lang.Best'),
                'updated' => Lang::get('placecompany.board::lang.Updated'),
                'viewed' => Lang::get('placecompany.board::lang.Viewed'),
                'yes' => Lang::get('placecompany.board::lang.Yes'),
                'no' => Lang::get('placecompany.board::lang.No'),
                'did_it_help' => Lang::get('placecompany.board::lang.Did it help?'),
                'hashtag' => Lang::get('placecompany.board::lang.Hashtag'),
                'tag' => Lang::get('placecompany.board::lang.Tag'),
                'add_a_tag' => Lang::get('placecompany.board::lang.Add a Tag'),
                'removing_tag' => Lang::get('placecompany.board::lang.Removing tag'),
                'changes_you_made_may_not_be_saved' => Lang::get('placecompany.board::lang.Changes you made may not be saved.'),
                'name' => Lang::get('placecompany.board::lang.Name'),
                'email' => Lang::get('placecompany.board::lang.Email'),
                'address' => Lang::get('placecompany.board::lang.Address'),
                'postcode' => Lang::get('placecompany.board::lang.Postcode'),
                'phone_number' => Lang::get('placecompany.board::lang.Phone number'),
                'mobile_phone' => Lang::get('placecompany.board::lang.Mobile phone'),
                'phone' => Lang::get('placecompany.board::lang.Phone'),
                'company_name' => Lang::get('placecompany.board::lang.Company name'),
                'vat_number' => Lang::get('placecompany.board::lang.Vat number'),
                'bank_account' => Lang::get('placecompany.board::lang.Bank account'),
                'name_of_deposit' => Lang::get('placecompany.board::lang.Name of deposit'),
                'find' => Lang::get('placecompany.board::lang.Find'),
                'rate' => Lang::get('placecompany.board::lang.Rate'),
                'ratings' => Lang::get('placecompany.board::lang.Ratings'),
                'waiting' => Lang::get('placecompany.board::lang.Waiting'),
                'complete' => Lang::get('placecompany.board::lang.Complete'),
                'question' => Lang::get('placecompany.board::lang.Question'),
                'answer' => Lang::get('placecompany.board::lang.Answer'),
                'notify_me_of_new_comments_via_email' => Lang::get('placecompany.board::lang.Notify me of new comments via email'),
                'ask_question' => Lang::get('placecompany.board::lang.Ask Question'),
                'categories' => Lang::get('placecompany.board::lang.Categories'),
                'pages' => Lang::get('placecompany.board::lang.Pages'),
                'all_products' => Lang::get('placecompany.board::lang.All Products'),
                'your_orders' => Lang::get('placecompany.board::lang.Your Orders'),
                'your_sales' => Lang::get('placecompany.board::lang.Your Sales'),
                'my_orders' => Lang::get('placecompany.board::lang.My Orders'),
                'my_sales' => Lang::get('placecompany.board::lang.My Sales'),
                'new_product' => Lang::get('placecompany.board::lang.New Product'),
                'edit_product' => Lang::get('placecompany.board::lang.Edit Product'),
                'delete_product' => Lang::get('placecompany.board::lang.Delete Product'),
                'seller' => Lang::get('placecompany.board::lang.Seller'),
                'period' => Lang::get('placecompany.board::lang.Period'),
                'period_of_use' => Lang::get('placecompany.board::lang.Period_of_use'),
                'last_updated' => Lang::get('placecompany.board::lang.Last updated'),
                'list_price' => Lang::get('placecompany.board::lang.List price'),
                'price' => Lang::get('placecompany.board::lang.price'),
                'total_price' => Lang::get('placecompany.board::lang.Total price'),
                'amount' => Lang::get('placecompany.board::lang.Amount'),
                'quantity' => Lang::get('placecompany.board::lang.Quantity'),
                'use_points' => Lang::get('placecompany.board::lang.Use points'),
                'my_points' => Lang::get('placecompany.board::lang.My points'),
                'available_points' => Lang::get('placecompany.board::lang.Available points'),
                'apply_points' => Lang::get('placecompany.board::lang.Apply points'),
                'buy_it_now' => Lang::get('placecompany.board::lang.Buy It Now'),
                'sold_out' => Lang::get('placecompany.board::lang.Sold Out'),
                'for_free' => Lang::get('placecompany.board::lang.For free'),
                'pay_s' => Lang::get('placecompany.board::lang.Pay :title', [
                    'title' => '%s'
                ]),
                'payment_method' => Lang::get('placecompany.board::lang.Payment method'),
                'credit_card' => Lang::get('placecompany.board::lang.Credit card'),
                'make_a_deposit' => Lang::get('placecompany.board::lang.Make a deposit'),
                'reward_point' => Lang::get('placecompany.board::lang.Reward point'),
                'download_expiry' => Lang::get('placecompany.board::lang.Download expiry'),
                'checkout' => Lang::get('placecompany.board::lang.Checkout'),
                'buyer_information' => Lang::get('placecompany.board::lang.Buyer information'),
                'applying_cash_receipts' => Lang::get('placecompany.board::lang.Applying cash receipts'),
                'applying_cash_receipt' => Lang::get('placecompany.board::lang.Applying cash receipt'),
                'cash_receipt' => Lang::get('placecompany.board::lang.Cash receipt'),
                'privacy_policy' => Lang::get('placecompany.board::lang.Privacy policy'),
                'i_agree_to_the_privacy_policy' => Lang::get('placecompany.board::lang.I agree to the privacy policy.'),
                'i_confirm_the_terms_of_the_transaction_and_agree_to_the_payment_process' =>
                    Lang::get('placecompany.board::lang.I confirm the terms of the transaction and agree to the payment process.'),
                'today' => Lang::get('placecompany.board::lang.Today'),
                'yesterday' => Lang::get('placecompany.board::lang.Yesterday'),
                'this_month' => Lang::get('placecompany.board::lang.This month'),
                'last_month' => Lang::get('placecompany.board::lang.Last month'),
                'last_30_days' => Lang::get('placecompany.board::lang.Last 30 days'),
                'agree' => Lang::get('placecompany.board::lang.Agree'),
                'disagree' => Lang::get('placecompany.board::lang.Disagree'),
                'opinion' => Lang::get('placecompany.board::lang.Opinion'),
                'comment' => Lang::get('placecompany.board::lang.Comment'),
                'comments' => Lang::get('placecompany.board::lang.Comments'),
                'your_order_has_been_cancelled' => Lang::get('placecompany.board::lang.Your order has been cancelled.'),
                'order_information_has_been_changed' => Lang::get('placecompany.board::lang.Order information has been changed.'),
                'order_date' => Lang::get('placecompany.board::lang.Order date'),
                'point_payment' => Lang::get('placecompany.board::lang.Point payment'),
                'cancel_point_payment' => Lang::get('placecompany.board::lang.Cancel point payment'),
                'paypal' => Lang::get('placecompany.board::lang.PayPal'),
                'point' => Lang::get('placecompany.board::lang.Point'),
                'zipcode' => Lang::get('placecompany.board::lang.Zip Code'),
                'this_year' => Lang::get('placecompany.board::lang.This year'),
                'last_year' => Lang::get('placecompany.board::lang.Last year'),
                'period_total' => Lang::get('placecompany.board::lang.Period total'),
                'total_revenue' => Lang::get('placecompany.board::lang.Total revenue'),
                'terms_of_service' => Lang::get('placecompany.board::lang.Terms of service'),
                'i_agree_to_the_terms_of_service' => Lang::get('placecompany.board::lang.I agree to the terms of service.'),
            ]
        ]);

        \Event::listen('cms.page.render', function (Controller $controller, $pageContents) use ($script) {
            echo "<script>{$script}</script>";
        });

    }

    public function onRender()
    {
        if(!$this->property('id')) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';

        if($this->property('blog')){
            \Session::put('board_switch_to_blog', $this->property('blog'));
            \Event::fire('placecompany.board.board_switch_to_blog', [$this->getProperties()]);
        }
        else{
            \Session::put('board_switch_to_blog', '');
        }

        $board = new BoardManager();
        $board->setID($this->property('id'));


        if($board->id){
            $builder = new BoardBuilderManager($board->id);
            $builder->board = $board;
            $builder->setSkin($board->skin);
            $builder->setRpp($board->page_rpp);
            $this->dirName = $builder->skin->dir($board->skin);

            if(isset($args['category1']) && $this->property('category1')){
                $builder->category1 = $this->property('category1');
            }
            if(isset($args['category2']) && $this->property('category2')){
                $builder->category2 = $this->property('category2');
            }

            $board = $builder->create();

            if($this->property('blog')){
                \Event::fire('placecompany.board.board_restore_current_blog', [$this->getProperties()]);
            }

            return $board;
        }
        else{
            if($this->property('blog')){
                \Event::fire('placecompany.board.board_restore_current_blog', [$this->getProperties()]);
            }

            return 'KBoard 알림 :: id='.$this->property('id').', 생성되지 않은 게시판입니다.';
        }
    }

    /**
     * 게시글 등록 및 수정
     */
    public function onEditorExecute()
    {
        $args = request();

        $id = isset($args['id'])?intval($args['id']):0;
        $board_id = isset($args['board_id'])?intval($args['board_id']):0;

        $content = new BoardContentManager();
        $content->initWithID($id);
        $content->setBoardID($board_id);
        $content->saveTemporary();
        $board = $content->getBoard();

        if(!$content->id && !$board->isWriter()){
            Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
            return redirect()->back();
        }
        else if($content->id && !$content->isEditor()){
            if($board->permission_write=='all' && !$content->user_id){
                if(!$content->isConfirm()){
                    Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
                    return redirect()->back();
                }
            }
            else{
                Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
                return redirect()->back();
            }
        }

        $content->new_password = isset($args['password'])?e($args['password']):$content->password;

        if(!$board->id){
            Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
            return redirect()->back();
        }
        else if(!$content->title){
            Flash::error(Lang::get('placecompany.board::lang.Please enter the title'));
            return redirect()->back();
        }
        else if(!BackEndAuth::check() && !$content->new_password){
            Flash::error(Lang::get('placecompany.board::lang.Please enter the password'));
            return redirect()->back();
        }

        // 금지단어 체크
        if(!$board->isAdmin()){
            $replace = array(' ', '「', '」', '『', '』', '-', '_', '.', '(', ')', '［', '］', ',', '~', '＊', '+', '^', '♥', '★', '!', '#', '=', '­', '[', ']', '/', '▶', '▷', '<', '>', '%', ':', 'ღ', '$', '*', '♣', '♧', '☞');

            // 작성자 금지단어 체크
            $name_filter = Helpers::board_name_filter(true);
            if($name_filter){
                $subject = urldecode($content->user_display);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $name_filter_message = Settings::get('board_name_filter_message', '');

                foreach($name_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$name_filter_message){
                            $name_filter_message = Lang::get(':title is not available.', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_name_filter_message', [&$name_filter_message, $filter, $subject, $board]);
                        Flash::error($name_filter_message);
                        return redirect()->back();
                    }
                }
            }

            // 본문/제목/댓글 금지단어 체크
            $content_filter = Helpers::board_content_filter(true);
            if($content_filter){
                $subject = urldecode($content->content);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $content_filter_message = Settings::get('board_content_filter_message', '');

                foreach($content_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$content_filter_message){
                            $content_filter_message = Lang::get(':title is not available.', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_content_filter_message', [&$content_filter_message, $filter, $subject, $board]);
                        Flash::error($content_filter_message);
                        return redirect()->back();
                    }
                }

                $subject = urldecode($content->title);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $content_filter_message = Settings::get('board_content_filter_message', '');

                foreach($content_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$content_filter_message){
                            $content_filter_message = Lang::get('placecompany.board::lang.:title is not available', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_content_filter_message', [&$content_filter_message, $filter, $subject, $board]);
                        Flash::error($content_filter_message);
                        return redirect()->back();
                    }
                }
            }
        }

        \Event::fire('placecompany.board.board_pre_content_execute', [$content, $board]);

        // 글쓰기 감소 포인트
        if($content->execute_action == 'insert' && $board->meta->document_insert_down_point){
            // @todo 포인트 구현 필요
        }

        // 실행
        $execute_id = $content->execute();

        if(!$execute_id){
            Flash::error(Lang::get('placecompany.board::lang.An unexpected problem has occurred.'));
            return redirect()->back();
        }

        \Event::fire('placecompany.board.board_content_execute', [$content, $board]);

        // 글쓰기 증가 포인트
        if($content->execute_action == 'insert' && $board->meta->document_insert_up_point){
            // @todo 포인트 구현 필요
        }

        // 비밀번호가 입력되면 즉시 인증과정을 거친다.
        if($content->password) $board->isConfirm($content->password, $execute_id);

        $url = new BoardUrlManager();

        if($content->execute_action == 'insert'){
            if(!$board->meta->after_executing_mod){
                $next_page_url = $url->set('execute_id', $execute_id)->set('id', $execute_id)->set('mod', 'document')->toString();
            }
            else{
                $next_page_url = $url->set('execute_id', $execute_id)->set('mod', $board->meta->after_executing_mod)->toString();
            }
        }
        else{
            $next_page_url = $url->set('id', $execute_id)->set('mod', 'document')->toString();
        }

        \Event::fire('placecompany.board.board_after_executing_url', [&$next_page_url, $execute_id, $board_id]);

        \Event::fire('placecompany.board.board_content_execute_pre_redirect', [$next_page_url, $content, $board]);

        if($content->execute_action == 'insert'){
            if($board->meta->conversion_tracking_code){
                echo $board->meta->conversion_tracking_code;
                echo "<script>window.location.href='{$next_page_url}';</script>";
                exit;
            }
        }

        return redirect($next_page_url);
    }

    /**
     * 게시글 좋아요
     */
    public function onDocumentLike(){
        if(input('document_id')){
            $content = new BoardContentManager();
            $content->initWithID(input('document_id'));
            if($content->id){
                $board = $content->getBoard();
                if($board->isVote()){
                    $args['target_id'] = $content->id;
                    $args['target_type'] = BoardVoteManager::$TYPE_DOCUMENT;
                    $args['target_vote'] = BoardVoteManager::$VOTE_LIKE;
                    $vote = new BoardVoteManager();
                    if(!$vote->isExists($args)){
                        if($vote->insert($args)){
                            $content->like += 1;
                            $content->vote = $content->like - $content->unlike;
                            $content->updateContent();

                            \Event::fire('placecompany.board.board_content_like', [$content, $board]);

                            return [
                                'result'=>'success',
                                'data'=> [
                                    'vote'=>intval($content->vote),
                                    'like'=>intval($content->vote),
                                    'unlike'=>intval($content->unlike)
                                ]
                            ];
                        }
                    }
                    else{
                        return [
                            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You have already voted.')
                        ];
                    }
                }
                else if(!BackendAuth::check()){
                    return [
                        'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.Please Log in to continue.')
                    ];
                }
            }
        }
        return [
            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You do not have permission.')
        ];
    }

    /**
     * 게시글 싫어요
     */
    function onDocumentUnlike(){
        if(input('document_id')){
            $content = new BoardContentManager();
            $content->initWithID(input('document_id'));
            if($content->id){
                $board = $content->getBoard();
                if($board->isVote()){
                    $args['target_id'] = $content->id;
                    $args['target_type'] = BoardVoteManager::$TYPE_DOCUMENT;
                    $args['target_vote'] = BoardVoteManager::$VOTE_UNLIKE;
                    $vote = new BoardVoteManager();
                    if($vote->isExists($args) === 0){
                        if($vote->insert($args)){
                            $content->unlike += 1;
                            $content->vote = $content->like - $content->unlike;
                            $content->updateContent();

                            \Event::fire('placecompany.board.board_content_unlike', [$content, $board]);

                            return [
                                'result'=>'success',
                                'data'=> [
                                    'vote'=>intval($content->vote),
                                    'like'=>intval($content->vote),
                                    'unlike'=>intval($content->unlike)
                                ]
                            ];
                        }
                    }
                    else{
                        return [
                            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You have already voted.')
                        ];
                    }
                }
                else if(!BackendAuth::check()){
                    return [
                        'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.Please Log in to continue.')
                    ];
                }
            }
        }
        return [
            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You do not have permission.')
        ];
    }


    /**
     * 미디어 파일 업로드
     */
    public function onMediaUpload(){
        $media = new BoardContentMediaManager();
        $media->board_id = intval(input('board_id')?:'');
        $media->media_group = input('board_id') ? Helpers::board_htmlclear((object)input('media_group')) : '';
        $media->content_id = intval(input('content_id')?:'');
        $media->upload();
    }
}
