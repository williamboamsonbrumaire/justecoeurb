// Initialisation Google Translate
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'fr',
    includedLanguages: 'fr,en,es,ht,pt',
    autoDisplay: false
  }, 'google_translate_element');
}

// Forcer la langue
function translateLanguage(lang) {
  const interval = setInterval(() => {
    const select = document.querySelector("select.goog-te-combo");
    if (select) {
      select.value = lang;
      select.dispatchEvent(new Event("change"));
      clearInterval(interval);
    }
  }, 200);
}
