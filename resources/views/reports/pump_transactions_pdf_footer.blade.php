        </tbody>
    </table>

    <div class="summary" style="background-color: #e3f2fd; padding: 15px; margin-top: 30px; border-radius: 5px;">
        <div class="summary-item" style="display: inline-block; margin-right: 30px;">
            <span class="summary-label" style="font-weight: bold; color: rgb(0, 0, 0);">Total Records:</span> {{ number_format($summary['total_records'] ?? 0) }}
        </div>
        <div class="summary-item" style="display: inline-block; margin-right: 30px;">
            <span class="summary-label" style="font-weight: bold; color: rgb(0, 0, 0);">Total Volume:</span> {{ number_format($summary['total_volume'] ?? 0, 2) }} L
        </div>
        <div class="summary-item" style="display: inline-block; margin-right: 30px;">
            <span class="summary-label" style="font-weight: bold; color: rgb(0, 0, 0);">Total Amount:</span> SAR {{ number_format($summary['total_amount'] ?? 0, 2) }}
        </div>
    </div>

    <div class="footer" style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #dee2e6; padding-top: 10px;">
        <p>© {{ now()->year }} Petrol Pump Station HOS - This is a system generated report</p>
    </div>
</body>
</html>


