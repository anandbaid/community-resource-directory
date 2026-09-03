<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\StaticPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ApiController extends Controller
{
    public function getSiteMenus(Request $request)
    {
        $providedToken = $request->header('X-API-TOKEN'); // Get token from headers
        $validToken = config('app.api_verification_token'); // Get valid token from config

        if ($providedToken !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API Token'
            ], 403);
        }

        $seoSettings = SiteSettings::whereIn('settings_name', [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'website_name',
        ])->pluck('settings_value', 'settings_name');

        $ogTitle = $request->input('og_title', $seoSettings['meta_title'] ?? config('app.name'));
        $ogDescription = $request->input('og_description', $seoSettings['meta_description'] ?? config('app.name'));
        $ogKeywords = $seoSettings['meta_keywords'] ?? '';
        $ogUrl = $request->input('og_url', url('/'));
        $ogType = $request->input('og_type', 'website');
        $rawOgImage = url('/assets/img/preview.jpg?v=2');
        $ogImage = filter_var($rawOgImage, FILTER_VALIDATE_URL) ? $rawOgImage : asset($rawOgImage);
        $ogSiteName = $seoSettings['website_name'] ?? config('app.name');

        $dynamicMeta = ''
            . '<meta name="description" content="' . e($ogDescription) . '">' . "\n"
            . '<meta name="keywords" content="' . e($ogKeywords) . '">' . "\n"
            . '<meta name="title" content="' . e($ogTitle) . '">' . "\n"
            . '<meta property="og:url" content="' . e($ogUrl) . '">' . "\n"
            . '<meta property="og:type" content="' . e($ogType) . '">' . "\n"
            . '<meta property="og:title" content="' . e($ogTitle) . '">' . "\n"
            . '<meta property="og:description" content="' . e($ogDescription) . '">' . "\n"
            . '<meta property="og:image" content="' . e($ogImage) . '">' . "\n"
            . '<meta property="og:image:width" content="1200" />' . "\n"
            . '<meta property="og:image:height" content="630" />' . "\n"
            . '<meta property="og:site_name" content="' . e($ogSiteName) . '">' . "\n"
            . '<meta name="twitter:card" content="summary_large_image">' . "\n"
            . '<meta name="twitter:title" content="' . e($ogTitle) . '">' . "\n"
            . '<meta name="twitter:description" content="' . e($ogDescription) . '">' . "\n"
            . '<meta name="twitter:image" content="' . e($ogImage) . '">';

        $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()
            ->settings_value ?? '';

        $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
        $footerLogo = SiteSettings::where('settings_name', 'footer_logo')->value('settings_value');
        $footerDescription = SiteSettings::where('settings_name', 'footer_description')->value('settings_value') ?? '';
        $hqAddress = SiteSettings::where('settings_name', 'footer_hq_address')->value('settings_value') ?? '';
        $hqPhoneRaw = SiteSettings::where('settings_name', 'footer_hq_phone')->value('settings_value') ?? '[]';
        $hqPhones = is_array($hqPhoneRaw) ? $hqPhoneRaw : json_decode($hqPhoneRaw, true);
        $hqPhones = is_array($hqPhones) ? array_values(array_filter($hqPhones)) : [];
        $hqPhoneHours = SiteSettings::where('settings_name', 'footer_hq_phone_hours')->value('settings_value') ?? '';
        $hqEmailRaw = SiteSettings::where('settings_name', 'footer_hq_email')->value('settings_value') ?? '[]';
        $hqEmails = is_array($hqEmailRaw) ? $hqEmailRaw : json_decode($hqEmailRaw, true);
        $hqEmails = is_array($hqEmails) ? array_values(array_filter($hqEmails)) : [];
        $mailingAddress = SiteSettings::where('settings_name', 'footer_mailing_address')->value('settings_value') ?? '';
        $mailingPhoneRaw = SiteSettings::where('settings_name', 'footer_mailing_phone')->value('settings_value') ?? '[]';
        $mailingPhones = is_array($mailingPhoneRaw) ? $mailingPhoneRaw : json_decode($mailingPhoneRaw, true);
        $mailingPhones = is_array($mailingPhones) ? array_values(array_filter($mailingPhones)) : [];
        $mailingPhoneHours = SiteSettings::where('settings_name', 'footer_mailing_phone_hours')->value('settings_value') ?? '';
        $mailingEmailRaw = SiteSettings::where('settings_name', 'footer_mailing_email')->value('settings_value') ?? '[]';
        $mailingEmails = is_array($mailingEmailRaw) ? $mailingEmailRaw : json_decode($mailingEmailRaw, true);
        $mailingEmails = is_array($mailingEmails) ? array_values(array_filter($mailingEmails)) : [];

        $footerLogoHtml = $footerLogo ? asset($footerLogo) : asset('assets/img/footer-logo.png');
        $footerDescriptionHtml = !empty($footerDescription)
            ? '<p class="mt-5">' . $footerDescription . '</p>'
            : '<p class="mt-5">Community Resource Directory, Inc. is a placeholder organisation name used for demonstration purposes.</p>';
        $hqAddressHtml = !empty($hqAddress)
            ? '<div class="fooIcoList loc">' . nl2br($hqAddress) . '</div>'
            : '<div class="fooIcoList loc">Community Resource Directory, Inc.<br> 123 Example Street, Suite 100<br>
                Anytown, ST 00000</div>';
        $hqPhonesHtml = !empty($hqPhones)
            ? '<div class="fooIcoList phn"><div>' . implode('', array_map(function ($phone) {
                $phoneHref = preg_replace('/\s+/', '', $phone);
                return '<div><a href="tel:' . e($phoneHref) . '">' . e($phone) . '</a></div>';
            }, $hqPhones)) . (!empty($hqPhoneHours) ? '<div class="small-font">' . e($hqPhoneHours) . '</div>' : '') . '</div></div>'
            : '<div class="fooIcoList phn"><div><a href="tel:(555) 000-0000">(555) 000-0000</a>
                <div class="small-font">Tue - Fri 10am to 6pm</div>
            </div></div>';
        $hqEmailsHtml = !empty($hqEmails)
            ? '<div class="fooIcoList mail"><div>' . implode('', array_map(function ($email) {
                return '<div><a href="mailto:' . e($email) . '">' . e($email) . '</a></div>';
            }, $hqEmails)) . '</div></div>'
            : '<div class="fooIcoList mail"><a href="mailto:info@example.org">info@example.org</a></div>';
        $mailingAddressHtml = !empty($mailingAddress)
            ? '<div class="fooIcoList loc mt-5">' . nl2br($mailingAddress) . '</div>'
            : '<div class="fooIcoList loc mt-5">Community Resource Directory, Inc.<br>456 Sample Avenue, Suite 200<br>Anytown, ST 00000</div>';
        $mailingPhonesHtml = !empty($mailingPhones)
            ? '<div class="fooIcoList phn"><div>' . implode('', array_map(function ($phone) {
                $phoneHref = preg_replace('/\s+/', '', $phone);
                return '<div><a href="tel:' . e($phoneHref) . '">' . e($phone) . '</a></div>';
            }, $mailingPhones)) . (!empty($mailingPhoneHours) ? '<div class="small-font">' . e($mailingPhoneHours) . '</div>' : '') . '</div></div>'
            : '';
        $mailingEmailsHtml = !empty($mailingEmails)
            ? '<div class="fooIcoList mail"><div>' . implode('', array_map(function ($email) {
                return '<div><a href="mailto:' . e($email) . '">' . e($email) . '</a></div>';
            }, $mailingEmails)) . '</div></div>'
            : '';

        $loginBlock =
            '<div class="logged-main">
                <div class="log-user-box">
                    <div class="logged-btn log-user-btn">
                        <i class="fa-solid fa-user log-user-icon"></i>
                        <span class="log-user-text logged-user-text">##UserName##</span>
                    </div>
                    </a>
                    <div class="logged-link">
                        <div class="df-column">
                            <div><a href="#" class="log-link"><i class="fa-regular fa-user"></i>View Profile</a></div>
                            <div><a href="' . url('logout') . '" class="log-link"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a></div>
                            <div class="register-mob-menu"><a href="' . url('saved-resources-view') . '" class="log-link"><i class="fa-regular fa-floppy-disk"></i>Saved Resources</a></div>
                            <div class="register-mob-menu"><a href="' . url('suggest-new-resources') . '" class="log-link"><i class="fa-regular fa-rectangle-list"></i>Recommend a New Resource</a></div>
                            <div class="register-mob-menu"><a href="' . url('suggest-existing-resources') . '" class="log-link"><i class="fa-regular fa-pen-to-square"></i>Suggest an Edit</a></div>
                        </div>
                    </div>
                </div>
                <div class="logged-toggler-box">
                    <div class="logged-toggler">
                        <i class="fa-solid fa-bars"></i>
                    </div>
                    <div class="logger-extra-link">
                        <div class="df-column">
                            <div><a href="' . url('saved-resources-view') . '" class="log-link"><i class="fa-regular fa-floppy-disk"></i> Saved Resources</a></div>
                            <div><a href="' . url('suggest-new-resources') . '" class="log-link"><i class="fa-regular fa-rectangle-list"></i>Recommend a New Resource</a></div>
                            <div><a href="' . url('suggest-existing-resources') . '" class="log-link"><i class="fa-regular fa-pen-to-square"></i>Suggest an Edit</a></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="toggler">
                <i class="fa-solid fa-bars"></i>
            </div>';
        $registerBlock =
            '<div class="logged-main">
                <div class="log-user-box">
                    <a href="' . url('register') . '" class="primary-btn log-user-btn">
                        <i class="fa-solid fa-user log-user-icon"></i>
                        <span class="log-user-text">Register</span>
                    </a>
                </div>
                <div class="log-user-box">
                    <a href="' . url('login') . '" class="secondary-btn log-user-btn">
                        <i class="fa-solid fa-right-to-bracket log-user-icon"></i>
                        <span class="log-user-text">Login</span>
                    </a>
                </div>
            </div>
            <div class="toggler">
                <i class="fa-solid fa-bars"></i>
            </div>';
        $dynamicHeaderPages = StaticPages::where('status', 'active')
            ->where('show_in_header', true)
            ->orderBy('header_order')
            ->orderBy('title')
            ->get()
            ->groupBy('header_parent');
        $footerPages = StaticPages::where('status', 'active')
            ->where('show_in_footer', true)
            ->orderByRaw('CASE WHEN footer_order IS NULL THEN 1 ELSE 0 END, footer_order ASC')
            ->get();

        $buildSubMenuItems = function ($pages) {
            $html = '';
            foreach ($pages as $page) {
                $title = e($page->title);
                $desc = e($page->header_menu_description ?: ('Learn more about ' . $page->title . '.'));
                $url = url($page->slug);
                $html .=
                    '<li class="col-xl-4 subMenuItemList">
                        <a href="' . $url . '" class="subMobItem">' . $title . '</a>
                        <div class="subMenuItem">
                            <h5>' . $title . '</h5>
                            <div class="subMenuItemPara">
                                <p>' . $desc . '</p>
                            </div>
                            <a href="' . $url . '">Learn More</a>
                        </div>
                    </li>';
            }
            return $html;
        };

        $aboutChildren = $dynamicHeaderPages['about-us'] ?? collect();
        $resourcesChildren = $dynamicHeaderPages['resources'] ?? collect();
        $shopChildren = $dynamicHeaderPages['shop'] ?? collect();
        $supportChildren = $dynamicHeaderPages['support'] ?? collect();
        $libraryChildren = $dynamicHeaderPages['library'] ?? collect();
        $blogChildren = $dynamicHeaderPages['blog'] ?? collect();

        $aboutChildrenHtml = $buildSubMenuItems($aboutChildren);
        $resourcesChildrenHtml = $buildSubMenuItems($resourcesChildren);
        $shopChildrenHtml = $buildSubMenuItems($shopChildren);
        $supportChildrenHtml = $buildSubMenuItems($supportChildren);
        $libraryChildrenHtml = $buildSubMenuItems($libraryChildren);
        $blogChildrenHtml = $buildSubMenuItems($blogChildren);

        $resources = SiteSettings::where('settings_name', 'resource_block')->first()->settings_value ?? '';
        $libraryContent = SiteSettings::where('settings_name', 'library_block')->first()->settings_value ?? '';
        $staticPage = StaticPages::where('slug', 'career-success-hub')->first();

        $footerLinksHtml = '';
        foreach ($footerPages as $page) {
            $footerLinksHtml .= '<li><a href="' . url($page->slug) . '">' . e($page->title) . '</a></li>';
        }

        $headerHtml =
            '<header>
                <section>
                    <div class="topNav" data-aos="fade-down">
                        <div class="container mainNavDiv">
                            <div class="headLogo">
                                <a href="' . url('/') . '"><img src="' . asset($headerLogo) . '" alt="logo"></a>
                            </div>
                            <nav class="navSidebar">
                                <div class="close"><i class="fa-solid fa-xmark"></i></div>
                                <ul class="mainNav">
                                    <li><a href="' . url('/') . '">Home</a></li>
                                    <li><a href="' . url('about-us') . '">About Us</a>
                                        <div class="sub-menu">
                                            <ul class="row sub-menu-back">
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('about-us') . '" class="subMobItem">Who We Are</a>
                                                    <div class="subMenuItem">
                                                        <h5>Who We Are</h5>
                                                        <div class="subMenuItemPara">
                                                            <p>Community Resource Directory is a sample organisation record used as placeholder
                                                            purpose
                                                            of
                                                            providing aid to individuals, families and communities in the United
                                                            States... </p>
                                                        </div>
                                                        <a href="' . url('about-us') . '">Learn More</a>
                                                    </div>
                                                </li>
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('about-us') . '/#our-team' . '" class="subMobItem">Our Team</a>
                                                    <div class="subMenuItem">
                                                        <h5>Our Team</h5>
                                                        <div class="subMenuItemPara">
                                                            <p>The daily operations of Community Resource Directory are managed by an
                                                                Executive
                                                                Director, various Departmental Directors, a Chief Financial Officer,
                                                                Secretary...</p>
                                                        </div>
                                                        <a href="' . url('about-us') . '/#our-team' . '">Learn More</a>
                                                    </div>
                                                </li>
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('about-us') . '/#regulatory-disclosure' . '" class="subMobItem">Regulatory Disclosure</a>
                                                    <div class="subMenuItem">
                                                        <h5>Regulatory Disclosure</h5>
                                                        <div class="subMenuItemPara">
                                                            <p>In compliance with applicable state and federal
                                                                regulations, Community Resource Directory makes disclosure of various
                                                                corporate...
                                                            </p>
                                                        </div>
                                                        <a href="' . url('about-us') . '/#regulatory-disclosure' . '">Learn More</a>
                                                    </div>
                                                </li>
                                                ' . $aboutChildrenHtml . '
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="' . url('search-resources') . '">Resources</a>
                                        <div class="sub-menu">
                                            <ul class="row sub-menu-back">
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('search-resources') . '" class="subMobItem">Resource Directory</a>
                                                    <div class="subMenuItem">
                                                        <h5>Resource Directory</h5>
                                                        <div class="subMenuItemPara">
                                                            ' . ($resources ?? "''") . '
                                                        </div>
                                                        <a href="' . url('search-resources') . '">Learn More</a>
                                                    </div>
                                                </li>
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('career-success-hub') . '" class="subMobItem">Career Success HUB</a>
                                                    <div class="subMenuItem">
                                                        <h5>' . ($staticPage->title ?? "''") . '</h5>
                                                        <div class="subMenuItemPara">
                                                            ' . ($staticPage->description ?? "''") . '
                                                        </div>
                                                        <a href="' . url('career-success-hub') . '">Learn More</a>
                                                    </div>
                                                </li>
                                                <li class="col-xl-4 subMenuItemList">
                                                    <a href="' . url('library') . '" class="subMobItem">Library</a>
                                                    <div class="subMenuItem">
                                                        <h5>Library</h5>
                                                        <div class="subMenuItemPara">
                                                            ' . ($libraryContent ?? "''") . '
                                                        </div>
                                                        <a href="' . url('library') . '">Learn More</a>
                                                    </div>
                                                </li>
                                                ' . $resourcesChildrenHtml . '
                                            </ul>
                                        </div>
                                    </li>
                                    <li><a href="https://shopre.org/">Shop</a>
                                        ' . (!empty($shopChildrenHtml) ? '<div class="sub-menu"><ul class="row sub-menu-back">' . $shopChildrenHtml . '</ul></div>' : '') . '
                                    </li>
                                    <li><a href="' . url('support-us') . '">Support</a>
                                        ' . (!empty($supportChildrenHtml) ? '<div class="sub-menu"><ul class="row sub-menu-back">' . $supportChildrenHtml . '</ul></div>' : '') . '
                                    </li>
                                    <li><a href="' . config('services.blog.url') . '" class="active">Blog</a>
                                        ' . (!empty($blogChildrenHtml) ? '<div class="sub-menu"><ul class="row sub-menu-back">' . $blogChildrenHtml . '</ul></div>' : '') . '
                                    </li>
                                </ul>
                            </nav>
                            ##LoginSection##
                        </div>
                    </div>
                </section>
            </header>';
        $footerHtml =
            '<footer>
                <aside>
                    <div class="container">
                        <div class="dropBox backEffect whiteTxt p-0">
                            <img src="' . asset('assets/img/donate_jars.jpg') . '" class="backImg">
                            <div class="row dropBoxCont flex-md-row flex-column-reverse">
                                <div class="col-md-7">
                                    <div class="dropBoxpad" data-aos="fade-right">
                                        <h2>Support <span class="lt">Us</span></h2>
                                        <p>By donating your time, resources or expertise to Community Resource Directory, you\'re taking an
                                            active role in our overall success and we thank you for your support!</p>
                                        <a href="https://example.org/donatehange-lives-and-strengthen-futures" class="secondary-btn donate-btn">Donate & Support</a>
                                    </div>
                                </div>
                                <div class="col-md-5 backgroundImg h-300" style="background-image: url(' . asset('assets/img/support_us.png') . ');"
                                    data-aos="fade-left">
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
                <section class="mainFootDiv afterDropPanel whiteTxt">
                    <div class="container panel-sm">
                        <div class="mainFoot">
                            <div class="row justify-content-between gy-3 flex-wrap">
                                <div class="col-auto mx-400" data-aos="fade-up">
                                    <div class="text-lg-start text-center">
                                        <img src="' . $footerLogoHtml . '" alt="logo">
                                    </div>
                                    ' . $footerDescriptionHtml . '
                                </div>
                                <div class="col-auto twoCol" data-aos="fade-up">
                                    <h5>Quick Links</h5>
                                    <ul class="footNav">
                                        <li><a href="' . url('search-resources') . '">Resources</a></li>
                                        <li><a href="' . url('career-success-hub') . '">Career Success HUB</a></li>
                                        <li><a href="' . url('our-partners') . '">Partners</a></li>
                                        <li><a href="https://shopre.org/">Shop</a></li>
                                        <li><a href="' . url('contact-us') . '">Contact</a></li>
                                        ' . $footerLinksHtml . '
                                    </ul>
                                </div>
                                <div class="col-auto mx-400 twoCol" data-aos="fade-up">
                                    ' . $hqAddressHtml . '
                                    ' . $hqPhonesHtml . '
                                    ' . $hqEmailsHtml . '
                                    ' . $mailingAddressHtml . '
                                    ' . $mailingPhonesHtml . '
                                    ' . $mailingEmailsHtml . '
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="copytight">
                        <div class="container">
                            <div
                                class="d-flex justify-content-lg-between justify-content-center gap-3 align-items-center flex-wrap ">
                                <div class="text-center" data-aos="fade-right" data-aos-offset="0">
                                    ' . $copyRight . '
                                </div>
                                <ul class="copyList" data-aos="fade-left" data-aos-offset="0">
                                    <li><a href="' . url('privacy-policy') . '">Privacy Policy</a></li>
                                    <li><a href="' . url('terms-conditions') . '">Terms & Conditions</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
                <aside>
                    <div class="footNotice blueBack whiteTxt">
                        <div class="container" data-aos="fade-up" data-aos-offset="0">
                            <p class="text-center mb-0">Are we missing a useful Resource? <a href="' . url('suggest-new-resources') . '">Let us know</a>.
                            </p>
                        </div>
                    </div>
                </aside>
            </footer>';

        return response()->json([
            'success' => true,
            'message' => 'API call successful!',
            'data' => ['headerHtml' => $headerHtml, 'footerHtml' => $footerHtml, 'loginBlock' => $loginBlock, 'registerBlock' => $registerBlock, 'dynamicMeta' => $dynamicMeta]
        ]);
    }
}
