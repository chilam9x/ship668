<style>
    .page-item.active .page-link {
        background-color: #261d29 !important;
        border-color: #261d29 !important;
    }
    .page-link {
        color: black;
    }
</style>
<nav aria-label="Page navigation example">
    <ul class="pagination">
        <li class="page-item {{ ($obj->currentPage() == 1) ? ' disabled' : '' }}">
            <a class="page-link" href="{{ $obj->url($obj->currentPage()-1) }}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
            </a>
        </li>
        @for ($i = 1; $i <= $obj->lastPage(); $i++)
            <li class="page-item {{ ($obj->currentPage() == $i) ? ' active' : '' }}"><a
                        class="page-link" href="{{ $obj->url($i) }}">{{ $i }}</a></li>
        @endfor
        <li class="page-item {{ ($obj->currentPage() == $obj->lastPage()) ? ' disabled' : '' }}">
            <a class="page-link" href="{{ $obj->url($obj->currentPage()+1) }}"
               aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
            </a>
        </li>
    </ul>
</nav>