# Honorlock Proctoring

> [!WARNING]
> **This plugin is deprecated and no longer supported.**
>
> The Honorlock Proctoring Service plugin (local_honorlockproctoring) works only with Moodle 3.9 to 4.3. Moodle no longer supports those versions, and this plugin will not receive further updates, including security fixes.
>
> To keep using Honorlock, switch to the **[Honorlock quiz access plugin (quizaccess_honorlock)](https://marketplace.moodle.com/plugins/quizaccess_honorlock)**, which supports Moodle 4.5 to 5.1. Follow the steps in [Switch to the Honorlock quiz access plugin](#switch-to-the-honorlock-quiz-access-plugin).

## Switch to the Honorlock quiz access plugin

### Before you start

- **Upgrade Moodle.** The quiz access plugin needs Moodle 4.5 or later. If your site runs an older version, upgrade Moodle first, following Moodle's upgrade path.
- **Plan a quiet window.** Proctoring is paused for affected quizzes until Honorlock finishes the switch. Avoid scheduling Honorlock-proctored exams during this time.
- **Check how you'll get a new client secret.** You'll use your existing client ID with a newly generated client secret. Don't generate the new secret yet: as soon as you do, this plugin stops working. Step 2 tells you when.
  - If you can sign in to Honorlock as an administrator, you can generate it yourself on the [API Clients](https://app.honorlock.com/organization/apiclients) page.
  - If you can't access Honorlock or the API Clients page, contact Honorlock support or your Customer Success Manager before you start. Agree on a time for them to generate the new secret and share it with you through a secure channel.

### Step 1: Replace this plugin with the quiz access plugin

1. Go to **Site administration > Plugins > Plugins overview**, find **Honorlock Proctoring Service (local_honorlockproctoring)** and click **Uninstall**. If Moodle asks you to, delete the `local/honorlockproctoring/` folder from your web server.
2. Go to **Site administration > Server > Web services > External services** and delete the service you created for Honorlock (suggested name: **Moodle API**). This also removes its token.
3. Keep the **honorlock_api** user and the **Honorlock API Access** role. The quiz access plugin reuses them when you activate it.
4. Install the **[Honorlock quiz access plugin (quizaccess_honorlock)](https://marketplace.moodle.com/plugins/quizaccess_honorlock)** using Moodle's plugin installer, or download it from Moodle Marketplace and place it in `mod/quiz/accessrule/honorlock/`.

### Step 2: Activate the quiz access plugin

1. Get a new client secret for your existing client ID. **Generating a new secret immediately stops this plugin from connecting to Honorlock.**
   - If you have access, go to [API Clients](https://app.honorlock.com/organization/apiclients) in Honorlock and generate a new client secret.
   - If you don't, ask Honorlock support or your Customer Success Manager to generate it now and share it with you through a secure channel.

   Copy the secret and store it safely to use in the following steps.
2. In Moodle, go to **Site administration > Plugins > Activity modules > Quiz > Honorlock Proctoring Service**.
3. Click **Activate**, enter your client ID and the new client secret, and click **Activate**.
4. Copy the installation configuration details shown. Activation sets up the web service account, a new token and the LTI tool for Honorlock.

### Step 3: Send Honorlock the new connection details

Contact Honorlock support and share the configuration details from Step 2: Platform ID, Client ID, Deployment ID, Public keyset URL, Access token URL, Authentication request URL and Token. Share the token only through the secure channel Honorlock support gives you, never by plain email or chat.

### Step 4: Turn proctoring back on for your quizzes

1. Wait for Honorlock to confirm the switch is complete. Honorlock then turns proctoring back on for your quizzes through the new plugin.
2. *Optional:* If instructors want students to take a quiz without proctoring while the switch is in progress, open the quiz and remove the password that starts with `HL_NO_EDIT` under **Extra restrictions on attempts**. Students can take that quiz unproctored until Honorlock turns proctoring back on.
3. Once the switch is complete, open each quiz that was proctored to check that Honorlock shows as enabled for each quiz before students take it.

Need help at any step? Contact Honorlock support. We're happy to help.

Thank you to every institution that used this plugin with Honorlock.

---

## Legacy documentation

The original installation and configuration guide for this plugin is kept for reference only in [LEGACY.md](LEGACY.md).
