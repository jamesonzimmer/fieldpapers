<form class="language" action="{$base_dir}/language.php" method="post">
    <p>
        Language:
        <input type="hidden" name="referer" value="{* http://{$domain|escape} *}{$request.uri|escape}"/>
        <button type="submit" class="{if $language == "en"}selected{/if}" name="language" value="en">English</button>
        <button type="submit" class="{if $language == "de"}selected{/if}" name="language" value="de">Deutsch</button>
        <button type="submit" class="{if $language == "nl"}selected{/if}" name="language" value="nl">Nederlands</button>
    </p>
</form>
