import {Outlet} from 'react-router';

export default function Layout() {
    return (
        <main className="App">
            <Outlet />
        </main>
    )
}