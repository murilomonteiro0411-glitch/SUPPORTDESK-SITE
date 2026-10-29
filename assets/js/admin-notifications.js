(() => {
  const bell = document.getElementById('notificationBell');
  const count = document.getElementById('notificationCount');
  const panel = document.getElementById('notificationPanel');
  if (!bell || !count || !panel) return;
  const esc = s => String(s).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
  async function loadNotifications(){
    try {
      const r = await fetch('notifications.php', {cache:'no-store'});
      const d = await r.json();
      const total = Number(d.new || 0);
      count.textContent = total;
      count.style.display = total ? 'grid' : 'none';
      bell.classList.toggle('has-high', Number(d.high || 0) > 0);
      panel.innerHTML = `<div class="notification-head"><b>Central de notificações</b><span>${d.high || 0} alta/urgente</span></div>` +
        (d.items.length ? d.items.map(i => `<a class="notification-item ${['Alta','Urgente'].includes(i.priority)?'notification-high':''}" href="ticket.php?id=${i.id}"><div><strong>${esc(i.subject)}</strong><small>#SD-${String(i.id).padStart(4,'0')} • ${esc(i.status)}</small></div><span>${esc(i.priority)}</span></a>`).join('') : '<div class="notification-empty">Nenhum chamado pendente.</div>');
    } catch(e) {}
  }
  bell.addEventListener('click', () => panel.classList.toggle('open'));
  loadNotifications();
  setInterval(loadNotifications, 15000);
})();
