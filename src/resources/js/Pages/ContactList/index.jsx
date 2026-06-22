import { Link, useNavigate, useLocation } from "react-router";
import { useEffect, useState, useRef } from "react";
import useAxiosPrivate from "../../Hooks/useAxiosPrivate";

const ContactLists = () => {
    const [contactLists, setContactLists] = useState();
    const [errMsg, setErrMsg] = useState('');
    const errRef = useRef();
    const axiosPrivate = useAxiosPrivate();
    const navigate = useNavigate();
    const location = useLocation();
    const effectRan = useRef(false);

    useEffect(() => {
        const controller = new AbortController();

        if(effectRan.current === false) {
            const getContactLists = async () => {
                try {
                    const response = await axiosPrivate.get('/contact-lists', {
                        signal: controller.signal
                    });
                    console.log(response?.data);
                    setContactLists(response?.data);
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
            };

            getContactLists();
            
            return () => {
                effectRan.current = true;
            }
        }
        
    }, []
    )
    
    return (
        <section>
            <h1>Contact lists Page</h1>
            {contactLists?.data?.length
                ? (
                    <ul>
                        {contactLists.data.map((contactList, i) => <li key={i}>{contactList?.name}</li>)}
                    </ul>
                ) : <p>No Lists to display</p>
            }
            <p ref={errRef} className={errMsg ? "errmsg" : "offscreen"}  aria-live="assertive">{errMsg}</p>
            <br />
            <div className="flexGrow">
                <Link to="/">Home</Link>
            </div>
        </section>
    )
}

export default ContactLists