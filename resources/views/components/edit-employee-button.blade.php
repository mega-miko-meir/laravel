@props(['employee'])

<a href="/edit-employee/{{ $employee->id }}"
   title="Редактировать сотрудника"
   style="width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;
          border-radius:7px;border:1px solid #e5e7eb;color:#6b7280;text-decoration:none;
          background:#fff;flex-shrink:0;"
   onmouseover="this.style.background='#f9fafb';this.style.color='#374151';"
   onmouseout="this.style.background='#fff';this.style.color='#6b7280';">
    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                 m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
</a>
