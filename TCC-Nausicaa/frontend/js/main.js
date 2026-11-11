// Espera o HTML carregar por completo antes de mexer nele
document.addEventListener("DOMContentLoaded", () => {
  const botao = document.getElementById("botao-teste");
  const resultado = document.getElementById("resultado-teste");

  // addEventListener: "quando clicar neste botão, execute esta função"
  botao.addEventListener("click", () => {
    resultado.textContent = "JavaScript funcionando! ✔";
  });
});