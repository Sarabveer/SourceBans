          <h3>{$lang_protest_ban|ucwords}</h3>
          <p>
            Before you proceed make sure you first check our ban list and search it by clicking <a href="{build_url _=banlist.php}">here</a> if you are listed and for what reason.
            If you do find yourself listed on the ban list and find the reason for this to be untrue you can write a protest.
          </p>
          <form action="{$active}" id="protest-main" method="post">
            <fieldset>
              <div>
                <label for="steam">Steam ID:</label>
                <input class="submit-fields" maxlength="32" {nid id="steam"} value="STEAM_" />
              </div>
              <div>
                <label for="ip">{$lang_ip_address}:</label>
                <input class="submit-fields" maxlength="15" {nid id="ip"} value="{$smarty.server.REMOTE_ADDR}" />
              </div>
              <div>
                <label for="name">{$lang_name}:</label>
                <input class="submit-fields" maxlength="64" {nid id="name"} />
                <span class="mandatory">*</span>
              </div>
              <div>
                <label for="reason">Reason why you should be unbanned (be as descriptive as possible):</label>
                <textarea class="submit-fields" {nid id="reason"} rows="5"></textarea>
                <span class="mandatory">*</span>
              </div>
              <div>
                <label for="email">{$lang_email_address}:</label>
                <input class="submit-fields" maxlength="128" {nid id="email"} />
                <span class="mandatory">*</span>
              </div>
              <span class="mandatory">*</span> = {$lang_mandatory}
              <input class="btn ok" type="submit" value="{$lang_submit}" />
            </fieldset>
            <h4>What happens after you posted your protest?</h4>
            <p>The admins will get notified of your protest. They will then review if the ban is conclusive. After reviewing you will get a reply, which usally means within 24 hours.</p>
            <p><strong>Note:</strong> Sending emails with threats to our admins, scolding or shouting will not get you unbanned and in fact we will delete your protest right away!</p>
          </form>