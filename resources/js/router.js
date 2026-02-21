import { createRouter, createWebHistory } from 'vue-router';
import FundListView from './views/FundListView.vue';
import FundForm from './components/FundForm.vue';
import DuplicateWarningList from './components/DuplicateWarningList.vue';

const routes = [
    {
        path: '/',
        name: 'fund-list',
        component: FundListView
    },
    {
        path: '/funds/create',
        name: 'fund-create',
        component: FundForm
    },
    {
        path: '/funds/:id/edit',
        name: 'fund-edit',
        component: FundForm,
        props: true
    },
    {
        path: '/duplicate-warnings',
        name: 'duplicate-warnings',
        component: DuplicateWarningList
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
