// delete
if (!Auth::isStaff()) { redirect('home', __('NO_PERMISSION'), 'warning'); }
ClientsLogic::delete((int) param(0, 0));

// submit
if (!Auth::isStaff()) { redirect('home', __('NO_PERMISSION'), 'warning'); }
if (!is_post() || !csrf_verify()) {
    redirect('client/view', __('NOTHING_SELECTED'), 'warning');
}
ClientsLogic::insert();