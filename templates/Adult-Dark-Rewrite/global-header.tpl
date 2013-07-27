<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>{$g_config.site_name}{if isset($title)} - {$title|t_chop(60,'...')}{/if}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="description" content="{if isset($meta_description)}{$meta_description}{else}{$g_config.meta_description}{/if}">
    <meta name="keywords" content="{if isset($meta_keywords)}{$meta_keywords}{else}{$g_config.meta_keywords}{/if}">
    <script type="text/javascript" src="{$g_config.base_uri}/js/jquery.js"></script>
    <script type="text/javascript" src="{$g_config.base_uri}/js/thumbs.js"></script>
    {if $g_loc_upload}
    <script type="text/javascript" src="{$g_config.base_uri}/js/jquery.form.js"></script>
    <script type="text/javascript" src="{$g_config.base_uri}/swfupload/swfupload.js"></script>
    {/if}
    {if $video_watch}
    <script type="text/javascript" src="{$g_config.template_uri}/js/video-watch.js"></script>
    {/if}
    <link rel="stylesheet" type="text/css" href="{$g_config.template_uri}/style.css" />
  </head>
  <body>

  <div id="non-footer">

    <div class="main-content" style="padding-top: 20px;">
      <div class="text-right bold fs105">
        {nocache}
        {if $g_logged_in}
        <a {if !$g_loc_my_account}href="{$g_config.base_uri}/user/my-account/"{/if}>{"_Text:My Account"}</a>
        |
        <a {if !$g_loc_upload}href="{$g_config.base_uri}/upload/"{/if}>{"_Text:Upload"}</a>
        |
        <a href="{$g_config.base_uri}/user/logout/">{"_Text:Logout"}</a>
        {else}
        <a {if !$g_loc_register}href="{$g_config.base_uri}/user/register/"{/if}>{"_Text:Sign Up"}</a>
        |
        <a {if !$g_loc_login}href="{$g_config.base_uri}/user/login/"{/if}>{"_Text:Log In"}</a>
        {/if}
        {/nocache}
      </div>


      <div id="main-header">
        <span id="main-header-left"></span>
        <span id="main-header-right"></span>

        <span class="links">
          <a href="{$g_config.base_uri}/">{"_Text:Home"}</a>
          <span class="dot"></span>
          <a href="{$g_config.base_uri}/videos/newest/">{"_Text:Newest Videos"}</a>
          <span class="dot"></span>
          <a href="{$g_config.base_uri}/videos/">{"_Text:Browse Videos"}</a>
          <span class="dot"></span>
          <a href="{$g_config.base_uri}/categories/">{"_Text:Categories"}</a>
        </span>

        <form action="{$g_config.base_uri}/index.php" method="get">
          <input type="text" size="30" name="term" value="{$g_term}" />
          <select name="c">
            <option value="0">-- {"_Text:All"} --</option>
            {categories var=$categories}
            {options from=$categories key=category_id value=name}
          </select>
          <input type="image" src="{$g_config.template_uri}/images/search-mag.png" name="b" style="position: relative; top: 6px; left: 4px;" />
          <input type="hidden" name="r" value="search" />
        </form>
      </div>
    </div>