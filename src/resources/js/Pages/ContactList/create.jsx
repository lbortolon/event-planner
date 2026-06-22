import {useRef, useState, useEffect} from 'react';
import { useNavigate, useLocation, Link } from "react-router";
import CreateContactListButton from '../../Components/ContactList/CreateContactListButton.jsx';
import useAxiosPrivate from "../../Hooks/useAxiosPrivate";

export default function CreateContactList() {
    const nameRef = useRef();
    const navigate = useNavigate();
    const location = useLocation();
    const axiosPrivate = useAxiosPrivate();

    const [name, setName] = useState('');
    const [errMsg, setErrMsg] = useState('');
    const [succMsg, setSuccMsg] = useState('');

    useEffect(() => {
        nameRef.current.focus();
    }, [])

    useEffect(() => {
        setErrMsg('');
    }, [name])

    const handleSubmit = async (e) => {
        e.preventDefault(); // ← blocca il comportamento default del form        
        const controller = new AbortController();
        try {
            const response = await axiosPrivate.post('/contact-lists', 
            {
                name: name,
            },
            {
                signal: controller.signal
            });
            setSuccMsg('List created');
            console.log(response?.data);
        } catch(err) {
            if(!err?.response) {
                setErrMsg('No Server response');
                console.error(err);
            } else if (err?.response?.status) {
                setErrMsg(err?.response?.status + ' ' + err?.response?.data?.message);
                console.error(err);
                navigate('/login', { state: { from: location }, replace: true });
            }
        }    
    }

    return (
            <div className="CreateContactList">
                    <section>
                        <h1>Create List</h1>
                        <h2>Messages</h2>
                            <p className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
                            <p className={succMsg ? "succmsg" : "offscreen"}  aria-live="assertive">{succMsg}</p>
                        
                        <h2>Form</h2>
                            <form onSubmit={handleSubmit}>
                                <label htmlFor="name">name</label>
                                <input 
                                    type="text" 
                                    id="name"
                                    ref={nameRef}
                                    autoComplete="off"
                                    onChange={(e) =>  setName(e.target.value)}
                                    value={name}
                                    required
                                />
                                
                                <CreateContactListButton />
                                
                            </form>
                            <br />
                        <h2>Links</h2>
                            <div className="flexGrow">
                                <Link to="/contact-lists">Go to the lists page</Link>
                            </div>
                            <div className="flexGrow">
                                <Link to="/">Home</Link>
                            </div>
                    </section>            
            </div>
        );
}
