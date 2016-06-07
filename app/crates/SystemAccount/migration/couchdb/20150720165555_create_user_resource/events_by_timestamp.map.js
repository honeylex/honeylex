function(event) {
    if (/^honeybee\.system_account\.user\-/.test(event._id) && event.seq_number) {
        emit(event.iso_date, 1);
    }
}
