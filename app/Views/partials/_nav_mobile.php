<div id="navMobile" class="nav-mobile">
<div class="nav-mobile-sc">
<div class="nav-mobile-inner">
<div class="row">
<div class="col-sm-12 mobile-nav-buttons">
<?php if ($generalSettings->multi_vendor_system == 1):
if (authCheck()): ?>
<a href="<?= generateDashUrl("add_product"); ?>" class="btn btn-md btn-custom btn-block"><?= trans("sell_now"); ?></a>
<?php else: ?>
<a href="javascript:void(0)" class="btn btn-md btn-custom btn-block close-menu-click" data-toggle="modal" data-target="#loginModal"><?= trans("sell_now"); ?></a>
<?php endif;
endif; ?>
</div>
</div>
<div class="row">
<div class="col-sm-12">
<div class="nav nav-tabs nav-tabs-mobile-menu" id="nav-tab" role="tablist">
<button class="nav-link active" data-toggle="tab" data-target="#tabMobileMainMenu" type="button" role="tab" aria-controls="main-menu"><?= trans("main_menu"); ?></button>
<button class="nav-link" id="nav-profile-tab" data-toggle="tab" data-target="#tabMobileCategories" type="button" role="tab" aria-controls="categories"><?= trans("categories"); ?></button>
</div>
<div class="tab-content tab-content-mobile-menu nav-mobile-links">
<div class="tab-pane fade show active" id="tabMobileMainMenu" role="tabpanel">
<ul id="navbar_mobile_links" class="navbar-nav">
<?php if (authCheck()): ?>
<li class="dropdown profile-dropdown nav-item">
<a href="#" class="dropdown-toggle image-profile-drop nav-link" data-toggle="dropdown" aria-expanded="false">
<?php if ($baseVars->unreadMessageCount > 0): ?>
<span class="message-notification message-notification-mobile"><?= $baseVars->unreadMessageCount; ?></span>
<?php endif; ?>
<img src="<?= getUserAvatar(user()); ?>" alt="<?= esc(getUsername(user())); ?>">
<?= esc(getUsername(user())); ?> <span class="icon-arrow-down"></span>
</a>
<ul class="dropdown-menu">
<?php if (isAdmin()): ?>
<li><a href="<?= adminUrl(); ?>"><i class="icon-admin"></i><?= trans("admin_panel"); ?></a></li>
<?php endif;
if (isVendor()): ?>
<li><a href="<?= dashboardUrl(); ?>"><i class="icon-dashboard"></i><?= trans("dashboard"); ?></a></li>
<?php endif; ?>
<li><a href="<?= generateProfileUrl(user()->slug); ?>"><i class="icon-user"></i><?= trans("profile"); ?></a></li>
<li>
<a href="<?= generateUrl('messages'); ?>"><i class="icon-mail"></i><?= trans("messages"); ?>&nbsp;
<?php if ($baseVars->unreadMessageCount > 0): ?>
<span class="span-message-count">(<?= $baseVars->unreadMessageCount; ?>)</span>
<?php endif; ?>
</a>
</li>
<li><a href="<?= generateUrl('settings', 'edit_profile'); ?>"><i class="icon-settings"></i><?= trans("settings"); ?></a></li>
<li>
<form action="<?= base_url('logout'); ?>" method="post" class="form-logout">
<?= csrf_field(); ?>
<input type="hidden" name="back_url" value="<?= getCurrentUrl(); ?>">
<button type="submit" class="btn-logout btn-logout-mobile"><i class="icon-logout"></i>&nbsp;&nbsp;<?= trans("logout"); ?></button>
</form>
</li>
</ul>
</li>
<?php endif; ?>
<li class="nav-item"><a href="<?= langBaseUrl(); ?>" class="nav-link"><?= trans("home"); ?></a></li>
<?php if (authCheck()): ?>
<li class="nav-item"><a href="<?= generateUrl('wishlist') . '/' . user()->slug; ?>" class="nav-link"><?= trans("wishlist"); ?></a></li>
<?php else: ?>
<li class="nav-item"><a href="<?= generateUrl('wishlist'); ?>" class="nav-link"><?= trans("wishlist"); ?></a></li>
<?php endif;
if (!empty($menuLinks)):
foreach ($menuLinks as $menuLink):
if ($menuLink->page_default_name == 'blog' || $menuLink->page_default_name == 'contact' || $menuLink->location == 'top_menu'):
$itemLink = generateMenuItemUrl($menuLink);
if (!empty($menuLink->page_default_name)):
$itemLink = generateUrl($menuLink->page_default_name);
endif; ?>
<li class="nav-item"><a href="<?= $itemLink; ?>" class="nav-link"><?= esc($menuLink->title); ?></a></li>
<?php endif;
endforeach;
endif;
if (!authCheck()): ?>
<li class="nav-item"><a href="javascript:void(0)" data-toggle="modal" data-target="#loginModal" class="nav-link close-menu-click"><?= trans("login"); ?></a></li>
<li class="nav-item"><a href="<?= generateUrl('register'); ?>" class="nav-link"><?= trans("register"); ?></a></li>
<?php endif;
if ($generalSettings->location_search_header == 1 && countItems($activeCountries) > 0): ?>
<li class="nav-item nav-item-messages">
<a href="javascript:void(0)" data-toggle="modal" data-target="#locationModal" class="nav-link btn-modal-location close-menu-click">
<i class="icon-map-marker float-left"></i>&nbsp;<?= !empty($baseVars->defaultLocationInput) ? $baseVars->defaultLocationInput : trans("location"); ?>
</a>
</li>
<?php endif; ?>
<li class="d-flex justify-content-center mobile-flex-dropdowns">
<?php if ($generalSettings->multilingual_system == 1 && countItems($activeLanguages) > 1): ?>
<div class="nav-item dropdown top-menu-dropdown">
<a href="javascript:void(0)" class="nav-link dropdown-toggle" data-toggle="dropdown">
<img src="<?= base_url($activeLang->flag_path); ?>" class="flag"><?= esc($activeLang->name); ?>&nbsp;<i class="icon-arrow-down"></i>
</a>
<ul class="dropdown-menu dropdown-menu-lang">
<?php foreach ($activeLanguages as $language): ?>
<li>
<a href="<?= convertUrlByLanguage($language); ?>" class="dropdown-item <?= $language->id == $activeLang->id ? 'selected' : ''; ?>">
<img src="<?= base_url($language->flag_path); ?>" class="flag"><?= esc($language->name); ?>
</a>
</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif;
if ($paymentSettings->currency_converter == 1 && !empty($currencies)): ?>
<div class="nav-item dropdown top-menu-dropdown">
<a href="javascript:void(0)" class="nav-link dropdown-toggle" data-toggle="dropdown">
<?= getSelectedCurrency()->code; ?>&nbsp;(<?= getSelectedCurrency()->symbol; ?>)&nbsp;<i class="icon-arrow-down"></i>
</a>
<form action="<?= base_url('set-selected-currency-post'); ?>" method="post">
<?= csrf_field(); ?>
<ul class="dropdown-menu">
<?php foreach ($currencies as $currency):
if ($currency->status == 1):?>
<li>
<button type="submit" name="currency" value="<?= $currency->code; ?>"><?= $currency->code; ?>&nbsp;(<?= $currency->symbol; ?>)</button>
</li>
<?php endif;
endforeach; ?>
</ul>
</form>
</div>
<?php endif; ?>
</li>
</ul>
</div>
<div class="tab-pane fade" id="tabMobileCategories" role="tabpanel">
<div id="navbar_mobile_back_button"></div>
<ul id="navbar_mobile_categories" class="navbar-nav navbar-mobile-categories">
<?php if (!empty($parentCategories)):
foreach ($parentCategories as $category):
if ($category->show_on_main_menu == 1):
if ($category->has_subcategory > 0): ?>
<li class="nav-item"><a href="javascript:void(0)" class="nav-link" data-id="<?= $category->id; ?>" data-parent-id="<?= $category->parent_id; ?>"><?= getCategoryName($category); ?><i class="icon-arrow-right"></i></a></li>
<?php else: ?>
<li class="nav-item"><a href="<?= generateCategoryUrl($category); ?>" class="nav-link"><?= getCategoryName($category); ?></a></li>
<?php endif;
endif; ?>
<?php endforeach;
endif; ?>
</ul>
</div>
</div>
</div>
</div>
</div>
</div>
</div>