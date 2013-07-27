{template file="global-header.tpl" title="_Text:Create an Account"}

<script language="JavaScript" type="text/javascript">
$(function()
{
    // Select date of birth fields
    $('select[name="birth_day"] option[value="{$g_form.birth_day}"]').attr('selected', 'selected');
    $('select[name="birth_month"] option[value="{$g_form.birth_month}"]').attr('selected', 'selected');
    $('select[name="birth_year"] option[value="{$g_form.birth_year}"]').attr('selected', 'selected');

    // Reload CAPTCHA image
    $('.captcha-reload')
    .click(function()
           {
               $(this)
               .siblings('.captcha-image')
               .attr('src', '{$g_config.base_uri}/code.php?' + Math.random());
           });

});
</script>

    <div class="main-content page-content">
      <span class="section-whole">

        <div class="section-content">
          <div class="header">
            <span class="header-left"></span>
            <span class="header-right"></span>
            <span class="header-text">{"_Text:Create an Account"}</span>
          </div>

          {if !empty($g_errors)}
          <div class="message-error">
            {"_Text:Please Fix"}
            <ul>
            {foreach var=$error from=$g_errors}
            <li>{$error}</li>
            {/foreach}
            </ul>
          </div>
          {/if}

          <form method="post" action="{$g_config.base_uri}/user/register/">

            <div class="field">
              <span class="label wider">{"_Label:Username"}:</span>
              <span class="input-container">
                <input type="text" name="username" size="30" value="{$g_form.username}" /><br />
                <span class="fs80">{"_Text:Username restriction"}</span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Choose a Password"}:</span>
              <span class="input-container">
                <input type="password" name="password" size="20" /><br />
                <span class="fs80">{"_Text:Password restriction"}</span>
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Confirm Password"}:</span>
              <span class="input-container">
                <input type="password" name="confirm_password" size="20" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:E-mail"}:</span>
              <span class="input-container">
                <input type="text" name="email" size="40" value="{$g_form.email}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Name"}:</span>
              <span class="input-container">
                <input type="text" name="name" size="40" value="{$g_form.name}" />
              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Birthday"}:</span>
              <span class="input-container">

                <select name="birth_month">
                  <option value="0">---</option>
                  <option value="01">{"_Text:January"}</option>
                  <option value="02">{"_Text:February"}</option>
                  <option value="03">{"_Text:March"}</option>
                  <option value="04">{"_Text:April"}</option>
                  <option value="05">{"_Text:May"}</option>
                  <option value="06">{"_Text:June"}</option>
                  <option value="07">{"_Text:July"}</option>
                  <option value="08">{"_Text:August"}</option>
                  <option value="09">{"_Text:September"}</option>
                  <option value="10">{"_Text:October"}</option>
                  <option value="11">{"_Text:November"}</option>
                  <option value="12">{"_Text:December"}</option>
                </select>

                <select name="birth_day">
                  <option value="0">---</option>
                  <option value="01">1</option>
                  <option value="02">2</option>
                  <option value="03">3</option>
                  <option value="04">4</option>
                  <option value="05">5</option>
                  <option value="06">6</option>
                  <option value="07">7</option>
                  <option value="08">8</option>
                  <option value="09">9</option>
                  <option value="10">10</option>
                  <option value="11">11</option>
                  <option value="12">12</option>
                  <option value="13">13</option>
                  <option value="14">14</option>
                  <option value="15">15</option>
                  <option value="16">16</option>
                  <option value="17">17</option>
                  <option value="18">18</option>
                  <option value="19">19</option>
                  <option value="20">20</option>
                  <option value="21">21</option>
                  <option value="22">22</option>
                  <option value="23">23</option>
                  <option value="24">24</option>
                  <option value="25">25</option>
                  <option value="26">26</option>
                  <option value="27">27</option>
                  <option value="28">28</option>
                  <option value="29">29</option>
                  <option value="30">30</option>
                  <option value="31">31</option>
                </select>

                <select name="birth_year">
                  <option value="0">---</option>
                  {range start=$g_year end=1900 counter=$year}
                  <option value="{$year}">{$year}</option>
                  {/range}
                </select>

              </span>
            </div>

            <div class="field">
              <span class="label wider">{"_Label:Gender"}:</span>
              <span class="input-container">
                <input type="radio" name="gender" value="Male" id="gender-male"{if $g_form.gender == 'Male'} checked="checked"{/if} /> <label for="gender-male">{"_Text:Male"}</label>
                <input type="radio" name="gender" value="Female" id="gender-female"{if $g_form.gender == 'Female'} checked="checked"{/if} /> <label for="gender-female">{"_Text:Female"}</label>
              </span>
            </div>

            {foreach var=$field from=$g_custom_fields}
            <div class="field">
              {if $field.type == 'Checkbox'}
              <span class="label wider"></span>
              <span class="input-container">{$field|t_formfield|rawhtml} {$field.label}</span>
              {else}
              <span class="label wider">{$field.label}:</span>
              <span class="input-container">{$field|t_formfield|rawhtml}</span>
              {/if}
            </div>
            {/foreach}

            {if $g_config.flag_captcha_on_signup}
            <div class="field">
              <span class="label wider">{"_Label:Verification"}:</span>
              <span class="input-container captcha-conatiner">
                <img src="{$g_config.base_uri}/code.php" class="captcha-image" />
                <img src="{$g_config.template_uri}/images/reload-22x22.png" class="captcha-reload" />
                <br />
                <input type="text" name="captcha" size="20" />
              </span>
            </div>
            {/if}

            <div class="field">
              <span class="label wider">{"_Label:Terms of Use"}:</span>
              <span class="input-container" style="width: 60%;">
                <input type="checkbox" name="terms" value="1" id="checkbox-terms"{if $g_form.terms} checked="checked"{/if} />
                <label for="checkbox-terms">{"_Text:I agree to terms and privacy",$g_config.base_uri,$g_config.base_uri}</label>

                <div style="margin-top: 8px;">
                  {"_Text:Copyright notice"}
                </div>
              </span>
            </div>

            <div class="field">
              <span class="label wider"></span>
              <span class="input-container">
                <input type="submit" value="{"_Button:Create Account"}" />
              </span>
            </div>

            <input type="hidden" name="r" value="register-submit" />
          </form>

        </div>

      </span>
    </div>

{template file="global-footer.tpl"}