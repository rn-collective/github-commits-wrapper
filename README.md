<div align='center'>
  <h1>GitHub Commits Wrapper - via Discord Webhooks</h1>
</div>

The provided repository code allows you to redirect requests from GitHub to Discord using webhooks, so you can customise your messages and use them to your advantage.  It is also worth noting that at the moment only push and merge events are supported, but you can add other events yourself to process them further by analysing the common template.

## How to install and use?
First you need to set up the configuration in the `config.php` file, at this point you only need two keys: `secret` and `webhook` fields.
The second step is to install these PHP scripts on your server for further processing, then bind the webhook in GitHub and configure it yourself.

## Useful links
<a href=https://autocode.com/tools/discord/embed-builder/>Discord Embed Builder</a><br>
<a href=https://docs.github.com/en/developers/webhooks-and-events/webhooks/about-webhooks>About webhooks (GitHub)</a><br>
<a href=https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks>About webhooks (Discord)</a>
