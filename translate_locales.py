import os
import json
import urllib.request
import urllib.parse
import time
import ssl

LOCALES = {
    "ar": "ar", "bg": "bg", "ca": "ca", "cs": "cs", "da": "da", "de": "de", 
    "es": "es", "fi": "fi", "fr": "fr", "fr_CA": "fr", "gl": "gl", "hy": "hy", 
    "it": "it", "ka": "ka", "mk": "mk", "ms": "ms", "nb_NO": "no", "nl": "nl", 
    "pt": "pt", "pt_BR": "pt", "ru": "ru", "sl": "sl", "sv": "sv", "tr": "tr", 
    "uk": "uk", "zh_Hans": "zh-CN"
}

KEYS = {
    "plugins.generic.aiReviewer.displayName": "AI Reviewer",
    "plugins.generic.aiReviewer.description": "A plugin to assist manuscript review using AI.",
    "plugins.generic.aiReviewer.settings.apiKey": "API Key",
    "plugins.generic.aiReviewer.settings.apiKey.description": "Enter your secret API key for the selected AI model.",
    "plugins.generic.aiReviewer.settings.aiModel": "AI Model",
    "plugins.generic.aiReviewer.button.description": "Automatically generate a review for this manuscript using artificial intelligence.",
    "plugins.generic.aiReviewer.button.generate": "Generate AI Review",
    "plugins.generic.aiReviewer.button.alert": "This feature is currently a placeholder for the future AI integration. Coming soon!",
    "plugins.generic.aiReviewer.button.loading": "Loading AI Review... This may take up to 60 seconds.",
    "plugins.generic.aiReviewer.button.saved": "The AI Review has been saved as a Submission File. Please refresh this page to view it in the Review Files grid.",
    "plugins.generic.aiReviewer.language": "Output Language",
    "plugins.generic.aiReviewer.settings.enableActivityLog": "Log AI Review generation in Activity Log"
}

def translate(text, target_lang):
    if target_lang == "en": return text
    if "AI Reviewer" == text and target_lang not in ["ar", "ru", "uk", "bg", "hy", "ka", "mk", "zh-CN"]:
        return text
    url = f"https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl={target_lang}&dt=t&q={urllib.parse.quote(text)}"
    try:
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, context=ctx) as response:
            res = json.loads(response.read().decode())
            return "".join([x[0] for x in res[0] if x[0]])
    except Exception as e:
        print(f"Error translating to {target_lang}: {e}")
        return text

for loc, lang_code in LOCALES.items():
    print(f"Translating for {loc}...")
    content = 'msgid ""\nmsgstr ""\n"Project-Id-Version: OJS AI Reviewer\\n"\n"MIME-Version: 1.0\\n"\n"Content-Type: text/plain; charset=UTF-8\\n"\n"Content-Transfer-Encoding: 8bit\\n"\n"Language: ' + loc + '\\n"\n\n'
    for key, text in KEYS.items():
        translated = translate(text, lang_code)
        translated = translated.replace('"', '\\"')
        content += f'msgid "{key}"\nmsgstr "{translated}"\n\n'
    
    os.makedirs(f"locale/{loc}", exist_ok=True)
    with open(f"locale/{loc}/locale.po", "w", encoding="utf-8") as f:
        f.write(content)
    time.sleep(0.5)

print("Done translating all locales!")
