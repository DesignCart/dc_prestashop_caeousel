{*
 * Admin configuration template for Design Cart Carousel
 * Tabs: Intro, Images, Appearance, Translations
 *}

{capture name=path}
  {$module->displayName|escape:'html':'UTF-8'}
{/capture}

<div class="panel">
  <div class="panel-heading">
    <img src="{$module_dir|escape:'html':'UTF-8'}logo.png" alt="" style="height:24px;vertical-align:middle;margin-right:8px;">
    {$module->displayName|escape:'html':'UTF-8'}
  </div>

  <ul class="nav nav-tabs" role="tablist" id="dc-carousel-tabs">
    <li class="active">
      <a href="#dc-tab-intro" role="tab" data-toggle="tab">{l s='Intro' mod='dc_carousel'}</a>
    </li>
    <li>
      <a href="#dc-tab-images" role="tab" data-toggle="tab">{l s='Obrazy' mod='dc_carousel'}</a>
    </li>
    <li>
      <a href="#dc-tab-appearance" role="tab" data-toggle="tab">{l s='Wygląd' mod='dc_carousel'}</a>
    </li>
    <li>
      <a href="#dc-tab-translations" role="tab" data-toggle="tab">{l s='Tłumaczenia' mod='dc_carousel'}</a>
    </li>
  </ul>

  <div class="tab-content" style="margin-top:15px;">
    {* Intro tab *}
    <div class="tab-pane active" id="dc-tab-intro">
      <form method="post" action="">
        <div class="form-group">
          <label class="control-label">{l s='Tytuł intro (H2)' mod='dc_carousel'}</label>
          <div class="translatable">
            {foreach from=$languages item=language}
              {assign var=id_lang value=$language.id_lang}
              <div class="lang_{$id_lang|intval} translatable-field row" style="{if $id_lang != $default_lang}display:none;{/if}">
                <div class="col-lg-9">
                  <input type="text"
                         name="DC_CAROUSEL_INTRO_TITLE_{$id_lang|intval}"
                         class="form-control"
                         value="{$intro.titles[$id_lang]|escape:'htmlall':'UTF-8'}">
                </div>
                <div class="col-lg-2">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    {$language.iso_code|escape:'html':'UTF-8'}
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    {foreach from=$languages item=language_flag}
                      <li>
                        <a href="javascript:void(0);" onclick="hideOtherLanguage({$language_flag.id_lang|intval});">
                          {$language_flag.iso_code|escape:'html':'UTF-8'}
                        </a>
                      </li>
                    {/foreach}
                  </ul>
                </div>
              </div>
            {/foreach}
          </div>
        </div>

        <div class="form-group">
          <label class="control-label">{l s='Opis intro' mod='dc_carousel'}</label>
          <div class="translatable">
            {foreach from=$languages item=language}
              {assign var=id_lang value=$language.id_lang}
              <div class="lang_{$id_lang|intval}" style="{if $id_lang != $default_lang}display:none;{/if}">
                <textarea name="DC_CAROUSEL_INTRO_DESC_{$id_lang|intval}" class="form-control" rows="4">{$intro.descriptions[$id_lang]|escape:'htmlall':'UTF-8'}</textarea>
              </div>
            {/foreach}
          </div>
        </div>

        <div class="panel-footer">
          <button type="submit" name="submitDcCarouselIntro" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Zapisz' mod='dc_carousel'}
          </button>
          <div class="clearfix"></div>
        </div>
      </form>
    </div>

    {* Images tab *}
    <div class="tab-pane" id="dc-tab-images">
      <div class="clearfix" style="margin-bottom:15px;">
        <button type="button" class="btn btn-primary pull-right" id="dc-add-image-btn">
          <i class="icon-plus"></i> {l s='Dodaj obraz' mod='dc_carousel'}
        </button>
      </div>

      <table class="table table-striped table-bordered" id="dc-carousel-images-table">
        <thead>
          <tr>
            <th style="width:40px;"></th>
            <th style="width:80px;">{l s='Obraz' mod='dc_carousel'}</th>
            <th>{l s='Tytuł' mod='dc_carousel'}</th>
            <th>{l s='Opis' mod='dc_carousel'}</th>
            <th>{l s='Link' mod='dc_carousel'}</th>
            <th style="width:120px;">{l s='Akcje' mod='dc_carousel'}</th>
          </tr>
        </thead>
        <tbody id="dc-carousel-images-tbody">
          {if $slides && count($slides)}
            {foreach from=$slides item=slide}
              <tr data-id="{$slide.id_dc_carousel_slide|intval}" class="dc-sortable-row">
                <td class="text-center dc-drag-handle">
                  <i class="icon-arrows"></i>
                </td>
                <td>
                  {if $slide.image}
                    <img src="{$slide.image|escape:'html':'UTF-8'}" alt="" style="max-width:70px;max-height:70px;">
                  {/if}
                </td>
                <td>{$slide.title|escape:'html':'UTF-8'}</td>
                <td>{$slide.description|truncate:80:'...'|escape:'html':'UTF-8'}</td>
                <td>{$slide.link|escape:'html':'UTF-8'}</td>
                <td class="text-right">
                  <button type="button" class="btn btn-default btn-xs dc-edit-image" data-id="{$slide.id_dc_carousel_slide|intval}">
                    <i class="icon-pencil"></i> {l s='Edytuj' mod='dc_carousel'}
                  </button>
                  <a href="{$current|escape:'html':'UTF-8'}&configure={$module->name|escape:'html':'UTF-8'}&token={$token|escape:'html':'UTF-8'}&deleteDcCarouselImage=1&id_dc_carousel_slide={$slide.id_dc_carousel_slide|intval}"
                     class="btn btn-danger btn-xs"
                     onclick="return confirm('{l s='Czy na pewno chcesz usunąć ten obraz?' mod='dc_carousel' js=1}');">
                    <i class="icon-trash"></i>
                  </a>
                </td>
              </tr>
            {/foreach}
          {else}
            <tr>
              <td colspan="6" class="text-center">
                {l s='Brak obrazów. Użyj przycisku „Dodaj obraz”.' mod='dc_carousel'}
              </td>
            </tr>
          {/if}
        </tbody>
      </table>

      {* Modal: dodawanie / edycja obrazu *}
      <div class="modal fade" id="dc-carousel-image-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="dc-carousel-modal-title" data-title-add="{l s='Dodaj obraz' mod='dc_carousel'}" data-title-edit="{l s='Edytuj obraz' mod='dc_carousel'}">{l s='Dodaj obraz' mod='dc_carousel'}</h4>
            </div>
            <form method="post" action=""
                  class="form-horizontal dc-filemanager"
                  data-dc-filemanager-api="{$dc_filemanager_api_url|escape:'html':'UTF-8'}"
                  data-dc-filemanager-base="{$dc_filemanager_base_url|escape:'html':'UTF-8'}"
                  data-dc-configure-url="{$configure_url_ajax|escape:'html':'UTF-8'}">
              <input type="hidden" name="id_dc_carousel_slide" id="dc_image_id" value="">
              <div class="modal-body">
                <div class="form-group">
                  <label class="control-label">{l s='Obraz' mod='dc_carousel'}</label>
                  <div class="input-group dc-input-group-image">
                    <span class="input-group-addon dc-fm-preview-wrap">
                      <img class="dc-fm-thumb-preview" src="" alt="" style="display:none;">
                    </span>
                    <input type="text" name="dc_carousel_image" id="dc_image_path" class="form-control dc-fm-input" value="">
                    <span class="input-group-btn">
                      <button class="btn btn-default dc-browse-image dc-fm-trigger" type="button">
                        <i class="icon-folder-open"></i> {l s='Przeglądaj' mod='dc_carousel'}
                      </button>
                    </span>
                  </div>
                  <p class="help-block">
                    {l s='Użyj menedżera plików Design Cart, aby wybrać obraz.' mod='dc_carousel'}
                  </p>
                </div>

                <div class="form-group">
                  <label class="control-label">{l s='Link (opcjonalnie)' mod='dc_carousel'}</label>
                  <input type="text" name="dc_carousel_link" id="dc_image_link" class="form-control" value="">
                </div>

                <hr>

                <div class="form-group">
                  <label class="control-label">{l s='Tytuł' mod='dc_carousel'}</label>
                  <div class="translatable">
                    {foreach from=$languages item=language}
                      {assign var=id_lang value=$language.id_lang}
                      <div class="lang_{$id_lang|intval}" style="{if $id_lang != $default_lang}display:none;{/if}">
                        <input type="text"
                               name="dc_carousel_title_{$id_lang|intval}"
                               id="dc_image_title_{$id_lang|intval}"
                               class="form-control"
                               value="">
                      </div>
                    {/foreach}
                  </div>
                </div>

                <div class="form-group">
                  <label class="control-label">{l s='Opis' mod='dc_carousel'}</label>
                  <div class="translatable">
                    {foreach from=$languages item=language}
                      {assign var=id_lang value=$language.id_lang}
                      <div class="lang_{$id_lang|intval}" style="{if $id_lang != $default_lang}display:none;{/if}">
                        <textarea name="dc_carousel_description_{$id_lang|intval}"
                                  id="dc_image_description_{$id_lang|intval}"
                                  class="form-control"
                                  rows="3"></textarea>
                      </div>
                    {/foreach}
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Anuluj' mod='dc_carousel'}</button>
                <button type="submit" name="submitDcCarouselImage" class="btn btn-primary">
                  <i class="process-icon-save"></i> {l s='Zapisz obraz' mod='dc_carousel'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {* Appearance tab *}
    <div class="tab-pane" id="dc-tab-appearance">
      <form method="post" action="">
        <h4>{l s='Wygląd intro' mod='dc_carousel'}</h4>
        <div class="row">
          <div class="col-lg-6">
            <h5>{l s='Tytuł (H2)' mod='dc_carousel'}</h5>
            <div class="form-group">
              <label>{l s='Rozmiar czcionki (px)' mod='dc_carousel'}</label>
              <input type="number" name="DC_CAROUSEL_INTRO_TITLE_FONT_SIZE" class="form-control"
                     value="{$appearance.DC_CAROUSEL_INTRO_TITLE_FONT_SIZE|intval}">
            </div>
            <div class="form-group">
              <label>{l s='Kolor' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_INTRO_TITLE_COLOR" class="form-control"
                     value="{$appearance.DC_CAROUSEL_INTRO_TITLE_COLOR|escape:'html':'UTF-8'}">
            </div>
            <div class="form-group">
              <label>{l s='Grubość czcionki' mod='dc_carousel'}</label>
              <select name="DC_CAROUSEL_INTRO_TITLE_WEIGHT" class="form-control">
                {foreach from=[200,300,400,500,600,700,800,900] item=weight}
                  <option value="{$weight}" {if $appearance.DC_CAROUSEL_INTRO_TITLE_WEIGHT == $weight}selected="selected"{/if}>{$weight}</option>
                {/foreach}
              </select>
            </div>
            <div class="form-group">
              <label>
                <input type="checkbox" name="DC_CAROUSEL_INTRO_TITLE_UPPERCASE" value="1"
                       {if $appearance.DC_CAROUSEL_INTRO_TITLE_UPPERCASE}checked="checked"{/if}>
                {l s='Duże litery' mod='dc_carousel'}
              </label>
            </div>
          </div>

          <div class="col-lg-6">
            <h5>{l s='Opis' mod='dc_carousel'}</h5>
            <div class="form-group">
              <label>{l s='Rozmiar czcionki (px)' mod='dc_carousel'}</label>
              <input type="number" name="DC_CAROUSEL_INTRO_DESC_FONT_SIZE" class="form-control"
                     value="{$appearance.DC_CAROUSEL_INTRO_DESC_FONT_SIZE|intval}">
            </div>
            <div class="form-group">
              <label>{l s='Kolor' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_INTRO_DESC_COLOR" class="form-control"
                     value="{$appearance.DC_CAROUSEL_INTRO_DESC_COLOR|escape:'html':'UTF-8'}">
            </div>
            <div class="form-group">
              <label>{l s='Grubość czcionki' mod='dc_carousel'}</label>
              <select name="DC_CAROUSEL_INTRO_DESC_WEIGHT" class="form-control">
                {foreach from=[200,300,400,500,600,700,800,900] item=weight}
                  <option value="{$weight}" {if $appearance.DC_CAROUSEL_INTRO_DESC_WEIGHT == $weight}selected="selected"{/if}>{$weight}</option>
                {/foreach}
              </select>
            </div>
            <div class="form-group">
              <label>
                <input type="checkbox" name="DC_CAROUSEL_INTRO_DESC_UPPERCASE" value="1"
                       {if $appearance.DC_CAROUSEL_INTRO_DESC_UPPERCASE}checked="checked"{/if}>
                {l s='Duże litery' mod='dc_carousel'}
              </label>
            </div>
          </div>
        </div>

        <hr>

        <h4>{l s='Ustawienia karuzeli' mod='dc_carousel'}</h4>
        <div class="row">
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Ilość na desktopie' mod='dc_carousel'}</label>
              <input type="number" min="1" name="DC_CAROUSEL_ITEMS_DESKTOP" class="form-control"
                     value="{$appearance.DC_CAROUSEL_ITEMS_DESKTOP|intval}">
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Ilość na tablecie' mod='dc_carousel'}</label>
              <input type="number" min="1" name="DC_CAROUSEL_ITEMS_TABLET" class="form-control"
                     value="{$appearance.DC_CAROUSEL_ITEMS_TABLET|intval}">
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Ilość na mobile' mod='dc_carousel'}</label>
              <input type="number" min="1" name="DC_CAROUSEL_ITEMS_MOBILE" class="form-control"
                     value="{$appearance.DC_CAROUSEL_ITEMS_MOBILE|intval}">
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Szybkość (ms)' mod='dc_carousel'}</label>
              <input type="number" min="100" step="100" name="DC_CAROUSEL_SPEED" class="form-control"
                     value="{$appearance.DC_CAROUSEL_SPEED|intval}">
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>
                <input type="checkbox" name="DC_CAROUSEL_AUTOPLAY" value="1"
                       {if $appearance.DC_CAROUSEL_AUTOPLAY}checked="checked"{/if}>
                {l s='Autoodtwarzanie' mod='dc_carousel'}
              </label>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>
                <input type="checkbox" name="DC_CAROUSEL_LOAD_OWL" value="1"
                       {if Configuration::get('DC_CAROUSEL_LOAD_OWL')}checked="checked"{/if}>
                {l s='Załaduj Owl Carousel (CSS/JS) z modułu' mod='dc_carousel'}
              </label>
              <p class="help-block">
                {l s='Włącz tylko, jeśli motyw/strona nie ładuje już Owl Carousel. Biblioteka (z katalogu modułu) zostanie dołączona tam, gdzie podpięty jest moduł.' mod='dc_carousel'}
              </p>
            </div>
          </div>
        </div>

        <hr>

        <h4>{l s='Tło modułu' mod='dc_carousel'}</h4>
        <div class="form-group">
          <label>{l s='Kolor tła' mod='dc_carousel'}</label>
          <input type="color" name="DC_CAROUSEL_BG_COLOR" class="form-control"
                 value="{$appearance.DC_CAROUSEL_BG_COLOR|escape:'html':'UTF-8'}">
        </div>

        <hr>

        <h4>{l s='Przyciski nawigacji' mod='dc_carousel'}</h4>
        <div class="row">
          <div class="col-lg-6">
            <h5>{l s='Normalne' mod='dc_carousel'}</h5>
            <div class="form-group">
              <label>{l s='Tło' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_NAV_BG" class="form-control"
                     value="{$appearance.DC_CAROUSEL_NAV_BG|escape:'html':'UTF-8'}">
            </div>
            <div class="form-group">
              <label>{l s='Kolor ikony' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_NAV_COLOR" class="form-control"
                     value="{$appearance.DC_CAROUSEL_NAV_COLOR|escape:'html':'UTF-8'}">
            </div>
          </div>
          <div class="col-lg-6">
            <h5>{l s='Po najechaniu' mod='dc_carousel'}</h5>
            <div class="form-group">
              <label>{l s='Tło' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_NAV_BG_HOVER" class="form-control"
                     value="{$appearance.DC_CAROUSEL_NAV_BG_HOVER|escape:'html':'UTF-8'}">
            </div>
            <div class="form-group">
              <label>{l s='Kolor ikony' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_NAV_COLOR_HOVER" class="form-control"
                     value="{$appearance.DC_CAROUSEL_NAV_COLOR_HOVER|escape:'html':'UTF-8'}">
            </div>
          </div>
        </div>

        <hr>

        <h4>{l s='Obrazy' mod='dc_carousel'}</h4>
        <div class="row">
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Proporcje obrazu' mod='dc_carousel'}</label>
              {assign var=ratios value=['1:1','3:1','4:3','16:9','2:3','3:4','9:16']}
              <select name="DC_CAROUSEL_IMG_RATIO" class="form-control">
                {foreach from=$ratios item=ratio}
                  <option value="{$ratio}" {if $appearance.DC_CAROUSEL_IMG_RATIO == $ratio}selected="selected"{/if}>{$ratio}</option>
                {/foreach}
              </select>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Kolor obramowania obrazu' mod='dc_carousel'}</label>
              <input type="color" name="DC_CAROUSEL_IMG_BORDER_COLOR" class="form-control"
                     value="{$appearance.DC_CAROUSEL_IMG_BORDER_COLOR|escape:'html':'UTF-8'}">
            </div>
          </div>
          <div class="col-lg-4">
            <div class="form-group">
              <label>{l s='Grubość obramowania (px)' mod='dc_carousel'}</label>
              <input type="number" name="DC_CAROUSEL_IMG_BORDER_WIDTH" class="form-control" min="0" step="1"
                     value="{$appearance.DC_CAROUSEL_IMG_BORDER_WIDTH|default:2|intval}">
            </div>
          </div>
        </div>

        <div class="panel-footer">
          <button type="submit" name="submitDcCarouselAppearance" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Zapisz wygląd' mod='dc_carousel'}
          </button>
          <div class="clearfix"></div>
        </div>
      </form>
    </div>

    {* Translations tab *}
    <div class="tab-pane" id="dc-tab-translations">
      <form method="post" action="">
        <div class="form-group">
          <label class="control-label">
            {l s='Etykieta linku w lightbox (np. „Przejdź do produktu”)' mod='dc_carousel'}
          </label>
          <div class="translatable">
            {foreach from=$languages item=language}
              {assign var=id_lang value=$language.id_lang}
              <div class="lang_{$id_lang|intval} translatable-field row" style="{if $id_lang != $default_lang}display:none;{/if}">
                <div class="col-lg-9">
                  <input type="text"
                         name="DC_CAROUSEL_LINK_LABEL_{$id_lang|intval}"
                         class="form-control"
                         value="{$translation_labels[$id_lang]|escape:'htmlall':'UTF-8'}">
                </div>
                <div class="col-lg-2">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    {$language.iso_code|escape:'html':'UTF-8'}
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu">
                    {foreach from=$languages item=language_flag}
                      <li>
                        <a href="javascript:void(0);" onclick="hideOtherLanguage({$language_flag.id_lang|intval});">
                          {$language_flag.iso_code|escape:'html':'UTF-8'}
                        </a>
                      </li>
                    {/foreach}
                  </ul>
                </div>
              </div>
            {/foreach}
          </div>
        </div>

        <div class="panel-footer">
          <button type="submit" name="submitDcCarouselTranslations" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Zapisz tłumaczenia' mod='dc_carousel'}
          </button>
          <div class="clearfix"></div>
        </div>
      </form>
    </div>
  </div>
</div>
