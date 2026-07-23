<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings;

final class ThemeSettingRenameMap
{
    public const RENAMED_SETTINGS = [
        'aNrofCatArticles' => 'numberOfCategoryProducts',
        'aNrofCatArticlesInGrid' => 'numberOfCategoryProductsInGrid',
        'blShowBirthdayFields' => 'showBirthdayFields',
        'blShowListDisplayType' => 'showListDisplayType',
        'blShowWeightInList' => 'showWeightInList',
        'iNewBasketItemMessage' => 'newBasketItemMessage',
        'sDefaultListDisplayType' => 'defaultListDisplayType',
        'bl_showManufacturer' => 'showManufacturer',
        'sShowBargainArticles' => 'showBargainProducts',
        'sShowNewestArticles' => 'showNewestProducts',
        'sShowTopArticles' => 'showTopProducts',
        'sProductListNavigation' => 'showProductListNavigation',
        'sShowPopBreadcrump' => 'showPopupBreadcrumb',
        'bl_showCompareList' => 'showCompareList',
        'bl_showGiftWrapping' => 'showGiftWrapping',
        'bl_showVouchers' => 'showVouchers',
        'bl_showWishlist' => 'showWishlist',
        'blEmailsShowProductPictures' => 'emailsShowProductPictures',
        'blFooterShowHelp' => 'footerShowHelp',
        'blFooterShowLinks' => 'footerShowLinks',
        'blFooterShowNewsletter' => 'footerShowNewsletter',
        'blFooterShowNewsletterForm' => 'footerShowNewsletterForm',
        'sBlogUrl' => 'blogUrl',
        'sFacebookUrl' => 'facebookUrl',
        'sInstagramUrl' => 'instagramUrl',
        'sTwitterUrl' => 'twitterUrl',
        'sYouTubeUrl' => 'youTubeUrl',
        'sPaymentIcons' => 'showPaymentIcons',
        'sTrustBadges' => 'showTrustBadges',
        'sDetailImageSize' => 'detailImageSize',
        'blSliderShowImageCaption' => 'sliderShowImageCaption',
        'sCatIconsize' => 'categoryIconSize',
        'sCatPromotionsize' => 'categoryPromotionSize',
        'sCatThumbnailsize' => 'categoryThumbnailSize',
        'sIconsize' => 'iconSize',
        'sManufacturerIconsize' => 'manufacturerIconSize',
        'sManufacturerPicturesize' => 'manufacturerPictureSize',
        'sManufacturerThumbnailsize' => 'manufacturerThumbnailSize',
        'sManufacturerPromotionsize' => 'manufacturerPromotionSize',
        'sThumbnailsize' => 'thumbnailSize',
        'sZoomImageSize' => 'zoomImageSize',
        'sEmailLogo' => 'emailLogo',
        'sLogoFile' => 'logoFile',
        'sLogoHeight' => 'logoHeight',
        'sLogoWidth' => 'logoWidth',
        'sFavicon16File' => 'favicon16File',
        'sFavicon32File' => 'favicon32File',
        'aAppleTouchIcon' => 'appleTouchIcon',
        'sFaviconFile' => 'faviconFile',
        'sFaviconSvg' => 'faviconSvg',
        'aOGImage' => 'openGraphImage',
        'sSiteManifestFile' => 'siteManifestFile',
        'sThemeColor' => 'themeColor',
        'blGAAnonymizeIPs' => 'googleAnalyticsAnonymizeIps',
        'blUseGAEcommerceTracking' => 'useGoogleAnalyticsEcommerceTracking',
        'blUseGAPageTracker' => 'useGoogleAnalyticsPageTracker',
        'sGATrackingId' => 'googleAnalyticsTrackingId',
        'sGoogleMapsAddr' => 'googleMapsAddress',
        'sBasketNoticeListButtonFunction' => 'basketNoticeListButtonFunction',
    ];

    public static function getNewName(string $name): string
    {
        return self::RENAMED_SETTINGS[$name] ?? $name;
    }
}
