<?php

if( !defined('TUBEX_CONTROL_PANEL') ) die('Invalid Access');

$DB = GetDB();
$schema = GetDBSchema();
$xtable = $schema->el('//table[name="'.$table.'"]');
$xnaming = $xtable->el('./naming');
$title_file = 'includes/cp-' . $xnaming->type . '-search-title.php';
$js_file = 'js/cp-' . $xnaming->type . '-search.js';
PrepareSearchAndSortFields($search_fields, $sort_fields, $xtable);


// Load predefined search
$saved_search = null;
if( ($pds = Request::Get('pds')) !== null )
{
    $xpds = $xtable->el('.//search[identifier="'.$pds.'"]');
    if( !empty($xpds) )
    {
        $saved_search['search_id'] = $xpds->id->val();
        $saved_search['identifier'] = $xpds->identifier->val();
        $saved_search['form'] = trim($xpds->json->val());

        foreach( $_REQUEST as $key => $value )
        {
            $saved_search['form'] = str_replace('##'.$key.'##', str_replace("'", "\\'", $value), $saved_search['form']);
        }
    }
}
// Load default search
else
{
    $saved_search = $DB->Row('SELECT * FROM `tbx_saved_search` WHERE `item_type`=? AND `identifier`=? ORDER BY `identifier`', array($table, SAVED_SEARCH_DEFAULT));
}

require_once('cp-global-header.php');
IncludeJavascript('js/cp-' . $xnaming->type . '-search.js');
?>

<script language="JavaScript" type="text/javascript">
var saved_search = <?php echo empty($saved_search) ? 'null' : $saved_search['form']; ?>;
var saved_search_id = <?php echo empty($saved_search) ? 'null' : $saved_search['search_id']; ?>;
var item_config = {text_lower: '<?php echo $xnaming->textLower; ?>',
                   text_lower_plural: '<?php echo $xnaming->textLowerPlural; ?>',
                   text_upper: '<?php echo $xnaming->textUpper; ?>',
                   text_upper_plural: '<?php echo $xnaming->textUpperPlural; ?>',
                   'function': '<?php echo $xnaming->function; ?>'};
</script>

<!-- BEGIN SEARCH FIELDS -->
<div class="centerer">
  <span class="centerer">
    <div class="header">Search <?php echo $xnaming->textUpperPlural; ?></div>

    <!-- START MASTER DIV FOR SEARCH FIELDS -->
    <div id="search-master">
      <select name="search_field[]" class="search-field">
        <?php echo Form_Field::Options($search_fields, null, 'column', 'label'); ?>
      </select>
      <select name="search_operator[]" class="search-operator">
        <?php
        $operators = array(SQL::LIKE => 'Contains',
                           SQL::NOT_LIKE => '!Contains',
                           SQL::EQUALS => '=',
                           SQL::NOT_EQUALS => '!=',
                           SQL::GREATER => '>',
                           SQL::GREATER_EQ => '>=',
                           SQL::LESS => '<',
                           SQL::LESS_EQ => '<=',
                           SQL::STARTS_WITH => 'Starts With',
                           SQL::NOT_STARTS_WITH => '!Starts With',
                           SQL::ENDS_WITH => 'Ends With',
                           SQL::NOT_ENDS_WITH => '!Ends With',
                           SQL::BETWEEN => 'Between',
                           SQL::NOT_BETWEEN => '!Between',
                           SQL::IN => 'In',
                           SQL::NOT_IN => '!In',
                           SQL::IS_EMPTY => 'Empty',
                           SQL::NOT_EMPTY => '!Empty',
                           SQL::IS_NULL => 'Is Null',
                           SQL::NOT_NULL => '!Null',
                           SQL::LENGTH_EQ => 'Length =',
                           SQL::LENGTH_GREATER => 'Length >',
                           SQL::LENGTH_LESS => 'Length <',
                           SQL::RLIKE => 'Regex',
                           SQL::NOT_RLIKE => '!Regex');

        echo Form_Field::Options($operators);
        ?>
      </select>
      <input type="text" name="search_term[]" class="search-term" value="" size="40" />
      <select name="search_connector[]" class="search-connector">
        <option value=""></option>
        <option value="AND">AND</option>
        <option value="OR">OR</option>
      </select>
    </div>
    <!-- END MASTER DIV FOR SEARCH FIELDS -->


    <!-- START MASTER DIV FOR SORT FIELDS -->
    <div id="sort-master">
      <select name="sort_field[]" class="sort-field">
        <?php echo Form_Field::Options($sort_fields, null, 'column', 'label'); ?>
      </select>
      <select name="sort_direction[]" class="sort-direction">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
      </select>
      <select name="sort_connector[]" class="sort-connector">
        <option value=""></option>
        <option value="then">then</option>
      </select>
    </div>
    <!-- END MASTER DIV FOR SORT FIELDS -->


    <!-- START SEARCH FORM -->
    <form method="post" action="ajax.php" id="search-form">
      <?php
      $xft = $xtable->el('.//fulltext');
      if( !empty($xft) ):
      ?>
      <div class="field">
        <label class="short">Text Search:</label>
        <span class="field-container">
        <input type="text" name="text_search" id="text-search" value="" size="60" />
        <select name="text_search_type">
          <option value="<?php echo SQL::FULLTEXT ?>">Natural Language</option>
          <option value="<?php echo SQL::FULLTEXT_BOOLEAN ?>">Boolean Mode</option>
        </select>
        </span>
      </div>
      <?php endif; ?>

      <div class="field">
        <label<?php if( !empty($xft) ): ?> style="padding-top: 0.7em;"<?php endif; ?> class="short">Search:</label>
        <span class="field-container field-separator" id="search-fields" <?php if( !empty($xft) ): ?> style="border-top: 1px solid #afafaf; padding-top: 5px;"<?php endif; ?>></span>
      </div>

      <div class="field">
        <label class="short">Sort By:</label>
        <span class="field-container" id="sort-fields"></span>
        <span class="field-container">
          <label>Per Page:</label>
          <input type="text" name="per_page" id="per-page" size="3" value="20" />
        </span>
      </div>

      <div class="field">
        <label class="short">&nbsp;</label>
        <span class="field-container">
          <input type="submit" id="search-button" value="Search" />

          <?php if( $xtable->el('./detailedOption') ): ?>
          <div class="checkbox" style="margin-left: 30px;">
            <input type="hidden" name="detailed" id="cb-detailed" value="" />
            Detailed View
          </div>
          <?php endif; ?>
        </span>
      </div>

      <input type="hidden" name="page" value="1" />
      <input type="hidden" name="table" id="table" value="<?php echo $table; ?>" />
      <input type="hidden" name="r" value="tbxGenericSearch" />
    </form>
    <!-- END SEARCH FORM -->

  </span>
</div>
<!-- END SEARCH FIELDS -->



<!-- BEGIN SEARCH RESULTS -->
<div class="search-results">
  <div class="search-results-header">
    Search Results <span class="search-start">?</span> - <span class="search-end">?</span> of <span class="search-total">?</span>
  </div>

  <div id="search-activity"><img src="images/activity-32x32.gif" border="0"><span>Searching...</span></div>
  <div id="search-no-results">No items matched the search term(s) you entered!</div>

  <div id="search-results-container">
    <table width="100%" cellpadding="0" cellspacing="0">
      <thead>
        <?php is_file($title_file) ? include_once($title_file) : null; ?>
      </thead>
      <tbody id="search-results-tbody">
      </tbody>
    </table>
  </div>

  <div class="search-results-footer">
    Search Results <span class="search-start">?</span> - <span class="search-end">?</span> of <span class="search-total">?</span>
  </div>
</div>
<!-- END SEARCH RESULTS -->



<!-- BEGIN SAVED SEARCHES -->
<div class="toolbar-tab" id="saved-searches-tab">
  <div class="toolbar-tab-container">
    <div class="toolbar-tab-tl"></div>
    <div class="toolbar-tab-tr"></div>
    <div class="toolbar-tab-t"></div>
    <div class="toolbar-tab-l"></div>
    <div class="toolbar-tab-r"></div>
    <div class="toolbar-tab-header">
      <img src="images/toolbar-tab-close-13x13.png" class="toolbar-tab-close" />
      Saved Searches
    </div>
    <div class="toolbar-tab-content">
      <div style="padding: 5px;">
        <span id="saved-searches-new">
          <form onsubmit="return false;">
            <b>New:</b> <input type="text" name="identifier" size="20" />

            <img src="images/save-22x22.png" class="icon-new clickable" title="Save" />

            <input type="hidden" name="type" value="<?php echo $table; ?>" />
            <input type="hidden" name="r" value="tbxSavedSearchAdd" />
          </form>
        </span>

        <span id="saved-searches-existing" style="margin-left: 20px;">
          <form onsubmit="return false;">
            <b>Existing:</b>
            <select name="id">
              <?php
              $saved_searches = $DB->FetchAll('SELECT * FROM `tbx_saved_search` WHERE `item_type`=? ORDER BY `identifier`', array($table));
              echo Form_Field::Options($saved_searches, null, 'search_id', 'identifier');
              ?>
            </select>

            <img src="images/open-22x22.png" class="icon-load clickable" title="Load" />
            <img src="images/save-22x22.png" class="icon-update clickable" title="Update" />
            <img src="images/delete-22x22.png" class="icon-delete clickable" title="Delete" />
          </form>
        </span>

        <img src="images/activity-grey-16x16.gif" class="toolbar-tab-activity" />
      </div>
    </div>
  </div>
</div>
<!-- END SAVED SEARCHES -->


<!-- BEGIN TOOLBAR -->
<div id="toolbar">
  <div id="toolbar-content">
    <div id="toolbar-select-all" class="toolbar-select-all selectable-master" title="Select/Deselect All"></div>

    <img src="images/saved-searches-32x32.png" class="toolbar-icon" id="saved-searches-icon" title="Saved Searches" meta="{t: 'custom'}" />
    <span class="toolbar-icon-separator"></span>

    <?php
    foreach( $xtable->xpath('./toolbar/icon') as $icon )
    {
        switch($icon->type->val())
        {
            case 'link':
                echo '<a href="'.$icon->link.'" target="'.$icon->target.'">' .
                     '<img src="images/'.$icon->img.'" border="0" class="toolbar-icon" title="'.$icon->title.'" /></a>' . String::NEWLINE_UNIX;
                break;

            case 'dialog':
                echo '<img src="images/'.$icon->img.'" border="0" class="toolbar-icon" title="'.$icon->title.'" meta="{t: \'dialog\', r: \''.$icon->function.'\'}" />' . String::NEWLINE_UNIX;
                break;

            case 'action':
                echo '<img src="images/'.$icon->img.'" border="0" class="toolbar-icon" title="'.$icon->title.'" meta="{t: \'action\', r: \''.$icon->function.'\'}" />' . String::NEWLINE_UNIX;
                break;

            case 'separator':
            default:
                echo '<span class="toolbar-icon-separator"></span>' . String::NEWLINE_UNIX;
                break;
        }
    }
    ?>

    <div id="toolbar-pagination">
      <img src="images/up-22x22.png" border="0" width="22" height="22" class="toolbar-top clickable" title="Top of Page" />
      <img src="images/pagination-first-off-22x22.png" border="0" width="22" height="22" id="search-page-first-off" />
      <img src="images/pagination-first-22x22.png" border="0" width="22" height="22" id="search-page-first" class="clickable" title="First Page" />
      <img src="images/pagination-prev-off-22x22.png" border="0" width="22" height="22" id="search-page-prev-off" />
      <img src="images/pagination-prev-22x22.png" border="0" width="22" height="22" id="search-page-prev" class="clickable" title="Previous Page" />
      <span id="page-jump"><input type="text" size="4" value="0" /> of <span id="search-pages">0</span></span>
      <img src="images/pagination-next-off-22x22.png" border="0" width="22" height="22" id="search-page-next-off" />
      <img src="images/pagination-next-22x22.png" border="0" width="22" height="22" id="search-page-next" class="clickable" title="Next Page" />
      <img src="images/pagination-last-off-22x22.png" border="0" width="22" height="22" id="search-page-last-off" />
      <img src="images/pagination-last-22x22.png" border="0" width="22" height="22" id="search-page-last" class="clickable" title="Last Page" />
    </div>
  </div>
</div>
<div class="toolbar-spacer"></div>
<!-- END TOOLBAR -->

<?php
require_once('cp-global-footer.php');
?>