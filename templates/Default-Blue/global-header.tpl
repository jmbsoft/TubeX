<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>{$g_config.site_name}{if isset($title)} - {$title}{/if}</title>
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

    <div class="main-content">
      <img src="{$g_config.template_uri}/images/tube.png" title="{$g_config.site_name}" alt="{$g_config.site_name}" id="logo" />
      <div id="navigation-tabs" class="center">
        <a {if !$g_loc_home}href="{$g_config.base_uri}/"{/if}>{"_Text:Home"}</a>
        <a {if !$g_loc_newest}href="{$g_config.base_uri}/index.php?r=videos-newest"{/if}>{"_Text:Newest Videos"}</a>
        <a {if !$g_loc_videos}href="{$g_config.base_uri}/index.php?r=videos"{/if}>{"_Text:Browse Videos"}</a>
        <a {if !$g_loc_categories}href="{$g_config.base_uri}/index.php?r=categories"{/if}>{"_Text:Categories"}</a>
        {nocache}
        {if $g_logged_in}
        <a {if !$g_loc_my_account}href="{$g_config.base_uri}/user.php?r=my-account"{/if}>{"_Text:My Account"}</a>
        <a {if !$g_loc_upload}href="{$g_config.base_uri}/upload.php"{/if}>{"_Text:Upload"}</a>
        <a href="{$g_config.base_uri}/user.php?r=logout">{"_Text:Logout"}</a>
        {else}
        <a {if !$g_loc_register}href="{$g_config.base_uri}/user.php?r=register"{/if}>{"_Text:Sign Up"}</a>
        <a {if !$g_loc_login}href="{$g_config.base_uri}/user.php?r=login"{/if}>{"_Text:Log In"}</a>
        {/if}
        {/nocache}
      </div>
    </div>

    <div id="search-bar">
      <form action="{$g_config.base_uri}/index.php" method="get">
        <input type="text" size="40" name="term" value="{$g_term}" />
        <input type="hidden" name="r" value="search" />
        <select name="c">
          <option value="0">-- {"_Text:All"} --</option>
          {categories var=$categories}
          {options from=$categories key=category_id value=name}
        </select>
        <input type="submit" name="b" value="{"_Button:Search"}" />
      </form>
    </div>