# Bin Camera Setup - Gemini API Key Configuration

## Quick Setup Guide

The bin camera requires a Gemini API key to classify waste items using AI.

---

## Step 1: Get Your Gemini API Key

1. Visit [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Sign in with your Google account
3. Click **"Get API Key"** or **"Create API Key"**
4. Copy the generated API key

---

## Step 2: Configure the API Key

1. Navigate to your project root folder: `c:\wamp64\www\APRecycle\`
2. Create a new file named `.env` (note the dot at the beginning)
3. Open `.env` with any text editor (Notepad, VS Code, etc.)
4. Add this line:
   ```
   GEMINI_API_KEY=your_api_key_here
   ```
5. Replace `your_api_key_here` with your actual API key (or use the api key provided below)
6. Save the file

---

## Example `.env` File

```
GEMINI_API_KEY=UR_API_KEY_HERE
```

---

## Step 3: Test the Bin Camera

1. Navigate to: `http://localhost/APRecycle/bin_camera/bin_camera.php`
2. Allow camera access when prompted
3. Capture a waste item image
4. The AI should classify the item automatically

---

## ⚠️ Important Notes

- The `.env` file must be in the project root directory (`c:\wamp64\www\APRecycle\`)
- Never share or commit the `.env` file to Git
- Make sure there are no extra spaces in the API key

---

## Troubleshooting

**API Key Not Working?**
- Double-check the API key is correct
- Ensure the `.env` file is named correctly (starts with a dot)
- Verify the file is in the correct location

**Camera Not Loading?**
- Use `localhost` or HTTPS (required for camera access)
- Check browser camera permissions
- Try using Google Chrome

---

That's it! Your bin camera should now be able to classify waste items using AI. 🎉
