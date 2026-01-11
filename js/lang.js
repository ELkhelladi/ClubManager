async function setLanguage(lang) {
  const response = await fetch(`xml/lang_${lang}.xml`);
  const text = await response.text();

  const parser = new DOMParser();
  const xml = parser.parseFromString(text, "text/xml");

  const title = xml.querySelector("text[id='title']").textContent;
  document.querySelector("h1").innerText = title;
}
