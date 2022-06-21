function cp_select_data(id){
	jQuery("#"+id).select();
    document.execCommand("copy");
}