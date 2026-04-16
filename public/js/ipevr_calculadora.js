/**
 * IPEVR GTC 45 - Calculadora de niveles de riesgo
 *
 * Espera que exista window.GTC45_CATALOGO con la estructura:
 *   { clasificaciones, peligros, nd, ne, nc, np, nr }
 *
 * Funciones expuestas en window.IPEVR:
 *   calcularNP(idNd, idNe)        -> { np, interpretacion }
 *   calcularNR(np, idNc)          -> { nr, interpretacion, aceptabilidad, color }
 *   interpretarNP(np)             -> objeto nivel_probabilidad
 *   interpretarNR(nr)             -> objeto nivel_riesgo
 *   valorDe(grupo, id)            -> valor numerico del catalogo por id
 */
(function () {
  const cat = window.GTC45_CATALOGO || {};

  function valorDe(grupo, id) {
    const lista = cat[grupo] || [];
    const it = lista.find(x => String(x.id) === String(id));
    return it ? Number(it.valor) : null;
  }

  function interpretarNP(np) {
    if (np == null || isNaN(np)) return null;
    return (cat.np || []).find(n => np >= Number(n.rango_min) && np <= Number(n.rango_max)) || null;
  }

  function interpretarNR(nr) {
    if (nr == null || isNaN(nr)) return null;
    return (cat.nr || []).find(n => nr >= Number(n.rango_min) && nr <= Number(n.rango_max)) || null;
  }

  function calcularNP(idNd, idNe) {
    const nd = valorDe('nd', idNd);
    const ne = valorDe('ne', idNe);
    if (nd == null || ne == null) return { np: null, interpretacion: null };
    const np = nd * ne;
    return { np, interpretacion: interpretarNP(np) };
  }

  function calcularNR(np, idNc) {
    const nc = valorDe('nc', idNc);
    if (np == null || nc == null) return { nr: null, interpretacion: null, aceptabilidad: null, color: null };
    const nr = np * nc;
    const i = interpretarNR(nr);
    return {
      nr,
      interpretacion: i,
      aceptabilidad: i ? i.aceptabilidad : null,
      color: i ? i.color_hex : null,
    };
  }

  window.IPEVR = { cat, calcularNP, calcularNR, interpretarNP, interpretarNR, valorDe };
})();
