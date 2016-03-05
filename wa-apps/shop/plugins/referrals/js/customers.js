$.customers.referralsAction = function (dummy, order) {
    order = this.getSortOrder(order);
    this.load('?plugin=referrals&module=customers' + order);
}