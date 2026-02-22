import { createRouter, createWebHistory } from 'vue-router';
import FundListView from './views/FundListView.vue';
import FundForm from './components/FundForm.vue';
import FundManagerForm from './components/FundManagerForm.vue';
import FundManagerList from './components/FundManagerList.vue';
import CompanyForm from './components/CompanyForm.vue';
import CompanyList from './components/CompanyList.vue';
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
        path: '/fund-managers',
        name: 'fund-manager-list',
        component: FundManagerList
    },
    {
        path: '/fund-managers/create',
        name: 'fund-manager-create',
        component: FundManagerForm
    },
    {
        path: '/companies',
        name: 'company-list',
        component: CompanyList
    },
    {
        path: '/companies/create',
        name: 'company-create',
        component: CompanyForm
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
