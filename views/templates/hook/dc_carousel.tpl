{if $dc_carousel_slides|@count > 0}
  {assign var=ap value=$dc_carousel_appearance}
  {assign var=ratio_css value=$ap.DC_CAROUSEL_IMG_RATIO|replace:':':' / '}
  <style type="text/css">
    .dc-carousel-wrapper .dc-nav { background-color: {$ap.DC_CAROUSEL_NAV_BG|escape:'html':'UTF-8'}; color: {$ap.DC_CAROUSEL_NAV_COLOR|escape:'html':'UTF-8'}; }
    .dc-carousel-wrapper .dc-nav:hover { background-color: {$ap.DC_CAROUSEL_NAV_BG_HOVER|escape:'html':'UTF-8'}; color: {$ap.DC_CAROUSEL_NAV_COLOR_HOVER|escape:'html':'UTF-8'}; }
  </style>
  <div class="dc-carousel-wrapper" style="background-color:{$ap.DC_CAROUSEL_BG_COLOR|escape:'html':'UTF-8'}">
    <div class="container">
      {assign var=intro value=$dc_carousel_intro}
      {assign var=current_lang value=$dc_carousel_lang_id|default:1}
      {if isset($intro.titles[$current_lang]) && $intro.titles[$current_lang] != '' || isset($intro.descriptions[$current_lang]) && $intro.descriptions[$current_lang] != ''}
        <div class="dc-carousel-intro">
          {assign var=title value=$intro.titles[$current_lang]}
          {assign var=desc value=$intro.descriptions[$current_lang]}
          {if $title}
            <h2 class="dc-carousel-title" style="font-size:{$ap.DC_CAROUSEL_INTRO_TITLE_FONT_SIZE|intval}px; color:{$ap.DC_CAROUSEL_INTRO_TITLE_COLOR|escape:'html':'UTF-8'}; font-weight:{$ap.DC_CAROUSEL_INTRO_TITLE_WEIGHT|escape:'html':'UTF-8'}; {if $ap.DC_CAROUSEL_INTRO_TITLE_UPPERCASE}text-transform:uppercase;{/if}">{$title|escape:'html':'UTF-8'}</h2>
          {/if}
          {if $desc}
            <div class="dc-carousel-desc" style="font-size:{$ap.DC_CAROUSEL_INTRO_DESC_FONT_SIZE|intval}px; color:{$ap.DC_CAROUSEL_INTRO_DESC_COLOR|escape:'html':'UTF-8'}; font-weight:{$ap.DC_CAROUSEL_INTRO_DESC_WEIGHT|escape:'html':'UTF-8'}; {if $ap.DC_CAROUSEL_INTRO_DESC_UPPERCASE}text-transform:uppercase;{/if}">{$desc nofilter}</div>
          {/if}
        </div>
      {/if}

      <div class="dc-carousel-outer">
        <div class="dc-carousel owl-carousel carousel-container" data-items-desktop="{$ap.DC_CAROUSEL_ITEMS_DESKTOP|intval}" data-items-tablet="{$ap.DC_CAROUSEL_ITEMS_TABLET|intval}" data-items-mobile="{$ap.DC_CAROUSEL_ITEMS_MOBILE|intval}" data-speed="{$ap.DC_CAROUSEL_SPEED|intval}" data-autoplay="{$ap.DC_CAROUSEL_AUTOPLAY|intval}">
          {foreach from=$dc_carousel_slides item=slide}
            <div class="dc-carousel-item">
              <a href="{$slide.image|escape:'html':'UTF-8'}"
                 class="dc-carousel-link glightbox"
                 data-gallery="dc-carousel"
                 data-title="{$slide.title|escape:'html':'UTF-8'}"
                 data-description="{$slide.description|escape:'htmlall':'UTF-8'}{if $slide.link && $dc_carousel_link_label}<br><br><a href='{$slide.link|escape:'html':'UTF-8'}' class='dc-carousel-lightbox-link'>{$dc_carousel_link_label|escape:'html':'UTF-8'}</a>{/if}">
                <div class="dc-carousel-image-wrapper" style="border-width:{$ap.DC_CAROUSEL_IMG_BORDER_WIDTH|default:2|intval}px; border-color:{$ap.DC_CAROUSEL_IMG_BORDER_COLOR|escape:'html':'UTF-8'}; aspect-ratio: {$ratio_css|escape:'html':'UTF-8'};">
                  <img src="{$slide.image|escape:'html':'UTF-8'}"
                       alt="{$slide.title|escape:'html':'UTF-8'}"
                       class="dc-carousel-image" loading="lazy">
                </div>
              </a>
            </div>
          {/foreach}
        </div>

        <button type="button" class="dc-nav dc-nav-prev" aria-label="Poprzedni">
          <span>&lsaquo;</span>
        </button>
        <button type="button" class="dc-nav dc-nav-next" aria-label="Następny">
          <span>&rsaquo;</span>
        </button>
      </div>
    </div>
  </div>
{/if}

