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

NEW_KEYS = {
    "plugins.generic.aiReviewer.settings.customPrompt": "Custom Prompt Instructions (Optional)",
    "plugins.generic.aiReviewer.settings.customPrompt.description": "Enter custom instructions or a specific rubric for the AI Reviewer. If left blank, the default 7-point academic review format will be used. You can specify focus areas or specific questions to ask the AI."
}

def translate(text, target_lang):
    if target_lang == "en": return text
    url = f"https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl={target_lang}&dt=t&q={urllib.parse.quote(text)}"
    try:
        ctx = ssl._create_unverified_context()
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, context=ctx, timeout=5) as response:
            res = json.loads(response.read().decode())
            return "".join([x[0] for x in res[0] if x[0]])
    except Exception as e:
        print(f"Error translating to {target_lang}: {e}")
        return text

for loc, lang_code in LOCALES.items():
    # Skip id_ID as we didn't include it in LOCALES anyway
    filepath = f"locale/{loc}/locale.po"
    if not os.path.exists(filepath):
        continue
        
    print(f"Translating new keys for {loc}...")
    append_content = "\n"
    for key, text in NEW_KEYS.items():
        translated = translate(text, lang_code)
        translated = translated.replace('"', '\\"')
        append_content += f'msgid "{key}"\nmsgstr "{translated}"\n\n'
    
    with open(filepath, "a", encoding="utf-8") as f:
        f.write(append_content)
    time.sleep(0.5)

print("Done translating new keys for all locales!")
