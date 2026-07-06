/* Caaguazú — mapa interactivo (Leaflet), cargado solo donde hay [caaguazu_mapa] */
(function(){
  if (typeof L === 'undefined') return;
  document.querySelectorAll('.caaguazu-map[data-points]').forEach(function(el){
    var points;
    try { points = JSON.parse(el.dataset.points); } catch (e) { return; }
    if (!points || !points.length) return;

    var center = [points[0].lat, points[0].lng];
    var map = L.map(el, { scrollWheelZoom: false }).setView(center, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(map);

    var bounds = [];
    points.forEach(function(p){
      var marker = L.marker([p.lat, p.lng]).addTo(map);
      marker.bindPopup('<strong>' + p.name + '</strong><br>' + (p.desc || ''));
      bounds.push([p.lat, p.lng]);
    });
    if (bounds.length > 1) map.fitBounds(bounds, { padding: [32, 32] });
  });
})();
